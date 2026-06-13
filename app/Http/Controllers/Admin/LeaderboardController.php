<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeaderboardExport;

class LeaderboardController extends Controller
{
    private function getLeaderboardData(?string $month)
    {
        $attempts = QuizAttempt::with(['user.location', 'quiz'])
            ->when($month, function ($query, $month) {
                return $query->where('month_year', $month);
            })
            ->get();

        $leaderboard = $attempts->groupBy('user_id')->map(function ($userAttempts) {
            $firstAttempt = $userAttempts->first();
            $user = $firstAttempt->user;

            if (!$user) {
                return null;
            }

            $totalScore = $userAttempts->sum('score');
            
            $totalQuestions = $userAttempts->sum(function ($attempt) {
                if (!$attempt->quiz) {
                    return $attempt->correct_answers;
                }
                return $attempt->quiz->is_daily_quiz 
                    ? ($attempt->quiz->daily_question_limit ?: 10) 
                    : $attempt->quiz->questions()->count();
            });

            $totalCorrect = $userAttempts->sum('correct_answers');
            $totalWrong = max(0, $totalQuestions - $totalCorrect);
            
            $percentageCorrect = $totalQuestions > 0 
                ? round(($totalCorrect / $totalQuestions) * 100, 2) 
                : 0;

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'nip' => $user->nip,
                'position' => $user->position,
                'location' => $user->location ? $user->location->name : 'Tidak Diketahui',
                'total_score' => $totalScore,
                'total_questions' => $totalQuestions,
                'total_correct' => $totalCorrect,
                'total_wrong' => $totalWrong,
                'percentage_correct' => $percentageCorrect,
            ];
        })
        ->filter()
        ->sortByDesc('total_score')
        ->values()
        ->map(function ($row, $index) {
            $row['rank'] = $index + 1;
            return $row;
        });

        return $leaderboard;
    }

    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $month = $request->filled('month') ? $request->input('month') : date('Y-m');

        // Fetch ALL leaderboard data first to calculate overall metrics and filters
        $fullLeaderboard = $this->getLeaderboardData($month);

        // Get unique locations from the full leaderboard for the dropdown filter
        $locations = $fullLeaderboard->pluck('location')
            ->filter(fn($loc) => $loc !== 'Tidak Diketahui')
            ->unique()
            ->sort()
            ->values();

        // Fetch all attempts in that month to calculate daily progress
        $attempts = QuizAttempt::with(['quiz'])
            ->when($month, function ($query, $month) {
                return $query->where('month_year', $month);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $parsedMonth = \Carbon\Carbon::parse($month . '-01');
        $daysInMonth = $parsedMonth->daysInMonth;

        $attemptsByDay = $attempts->groupBy(function ($attempt) {
            return $attempt->created_at->format('d');
        });

        $dailyProgressData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
            $dayAttempts = $attemptsByDay->get($dayStr);

            if ($dayAttempts && $dayAttempts->count() > 0) {
                $avgScore = $dayAttempts->avg('score');
                $totalCorrect = $dayAttempts->sum('correct_answers');
                $totalQuestions = $dayAttempts->sum(function ($attempt) {
                    if (!$attempt->quiz) {
                        return $attempt->correct_answers;
                    }
                    return $attempt->quiz->is_daily_quiz 
                        ? ($attempt->quiz->daily_question_limit ?: 10) 
                        : $attempt->quiz->questions()->count();
                });
                $avgAccuracy = $totalQuestions > 0 
                    ? round(($totalCorrect / $totalQuestions) * 100, 2)
                    : 0;
            } else {
                $avgScore = null;
                $avgAccuracy = null;
            }

            $dailyProgressData[] = [
                'day' => $dayStr,
                'avg_score' => $avgScore !== null ? round($avgScore, 2) : null,
                'avg_accuracy' => $avgAccuracy,
                'total_attempts' => $dayAttempts ? $dayAttempts->count() : 0,
            ];
        }

        // Pie Chart Data: Overall correct vs wrong (calculated from full leaderboard)
        $totalCorrectAll = $fullLeaderboard->sum('total_correct');
        $totalWrongAll = $fullLeaderboard->sum('total_wrong');
        $pieChartData = [
            'correct' => $totalCorrectAll,
            'wrong' => $totalWrongAll,
        ];

        // Apply filters & sorting for the paginated leaderboard table
        $filteredLeaderboard = $fullLeaderboard;

        $search = $request->input('search');
        if ($search) {
            $searchLower = strtolower(trim($search));
            $filteredLeaderboard = $filteredLeaderboard->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['name'] ?? ''), $searchLower) ||
                       str_contains(strtolower($row['position'] ?? ''), $searchLower) ||
                       str_contains(strtolower($row['location'] ?? ''), $searchLower);
            });
        }

        $locationFilter = $request->input('location');
        if ($locationFilter) {
            $filteredLeaderboard = $filteredLeaderboard->filter(function ($row) use ($locationFilter) {
                return ($row['location'] ?? '') === $locationFilter;
            });
        }

        $sortKey = $request->input('sort_key', 'score');
        $sortDir = $request->input('sort_dir', 'desc');

        $sortMap = [
            'score' => 'total_score',
            'total_quizzes' => 'total_questions',
            'correct' => 'total_correct',
            'wrong' => 'total_wrong',
            'accuracy' => 'percentage_correct',
        ];

        $sortByField = $sortMap[$sortKey] ?? 'total_score';
        
        $filteredLeaderboard = $filteredLeaderboard->sortBy(function ($row) use ($sortByField) {
            return $row[$sortByField];
        }, SORT_REGULAR, $sortDir === 'desc')->values();

        // Paginate the collection
        $perPage = 10;
        $page = $request->input('page', 1);
        $paginatedLeaderboard = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredLeaderboard->forPage($page, $perPage)->values(),
            $filteredLeaderboard->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Admin/Leaderboard/Index', [
            'leaderboard' => $paginatedLeaderboard,
            'locations' => $locations,
            'dailyProgressData' => $dailyProgressData,
            'pieChartData' => $pieChartData,
            'filters' => [
                'month' => $month,
                'search' => $search,
                'location' => $locationFilter,
                'sort_key' => $sortKey,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function export(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $month = $request->filled('month') ? $request->input('month') : date('Y-m');

        $fullLeaderboard = $this->getLeaderboardData($month);

        // Apply filters
        $filteredLeaderboard = $fullLeaderboard;

        $search = $request->input('search');
        if ($search) {
            $searchLower = strtolower(trim($search));
            $filteredLeaderboard = $filteredLeaderboard->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['name'] ?? ''), $searchLower) ||
                       str_contains(strtolower($row['position'] ?? ''), $searchLower) ||
                       str_contains(strtolower($row['location'] ?? ''), $searchLower);
            });
        }

        $locationFilter = $request->input('location');
        if ($locationFilter) {
            $filteredLeaderboard = $filteredLeaderboard->filter(function ($row) use ($locationFilter) {
                return ($row['location'] ?? '') === $locationFilter;
            });
        }

        $sortKey = $request->input('sort_key', 'score');
        $sortDir = $request->input('sort_dir', 'desc');

        $sortMap = [
            'score' => 'total_score',
            'total_quizzes' => 'total_questions',
            'correct' => 'total_correct',
            'wrong' => 'total_wrong',
            'accuracy' => 'percentage_correct',
        ];

        $sortByField = $sortMap[$sortKey] ?? 'total_score';
        
        $filteredLeaderboard = $filteredLeaderboard->sortBy(function ($row) use ($sortByField) {
            return $row[$sortByField];
        }, SORT_REGULAR, $sortDir === 'desc')->values();

        $filename = 'leaderboard';
        if ($month) {
            $filename .= '-' . $month;
        }
        $filename .= '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new LeaderboardExport($filteredLeaderboard),
            $filename
        );
    }
}
