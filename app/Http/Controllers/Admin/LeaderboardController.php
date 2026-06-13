<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeaderboardExport;

class LeaderboardController extends Controller
{
    private function getDailyLeaderboardData(?string $month)
    {
        $attempts = QuizAttempt::with(['user.location', 'quiz'])
            ->whereHas('quiz', function ($q) {
                $q->where('is_daily_quiz', 1);
            })
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
                return $attempt->quiz->daily_question_limit ?: 10;
            });

            $totalCorrect = $userAttempts->sum('correct_answers');
            $totalWrong = max(0, $totalQuestions - $totalCorrect);
            
            $percentageCorrect = $totalQuestions > 0 
                ? round(($totalCorrect / $totalQuestions) * 100, 2) 
                : 0;

            // Attendance: count distinct dates of attempt creation
            $attendance = $userAttempts->map(function ($attempt) {
                return $attempt->created_at->format('Y-m-d');
            })->unique()->count();

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
                'attendance' => $attendance,
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

    private function getEventLeaderboardData(?int $eventId)
    {
        if (!$eventId) return collect([]);

        $attempts = QuizAttempt::with(['user.location', 'quiz'])
            ->where('quiz_id', $eventId)
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
                return $attempt->quiz->questions()->count();
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

    public function dailyIndex(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $month = $request->filled('month') ? $request->input('month') : date('Y-m');

        $fullLeaderboard = $this->getDailyLeaderboardData($month);

        $locations = $fullLeaderboard->pluck('location')
            ->filter(fn($loc) => $loc !== 'Tidak Diketahui')
            ->unique()
            ->sort()
            ->values();

        $attempts = QuizAttempt::with(['quiz'])
            ->whereHas('quiz', function ($q) {
                $q->where('is_daily_quiz', 1);
            })
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
                    if (!$attempt->quiz) return $attempt->correct_answers;
                    return $attempt->quiz->daily_question_limit ?: 10;
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

        $totalCorrectAll = $fullLeaderboard->sum('total_correct');
        $totalWrongAll = $fullLeaderboard->sum('total_wrong');
        $pieChartData = [
            'correct' => $totalCorrectAll,
            'wrong' => $totalWrongAll,
        ];

        $filteredLeaderboard = $this->applyFilters($request, $fullLeaderboard);

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

        return Inertia::render('Admin/Leaderboard/Daily', [
            'leaderboard' => $paginatedLeaderboard,
            'locations' => $locations,
            'dailyProgressData' => $dailyProgressData,
            'pieChartData' => $pieChartData,
            'filters' => [
                'month' => $month,
                'search' => $request->input('search'),
                'location' => $request->input('location'),
                'sort_key' => $request->input('sort_key', 'score'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    public function eventIndex(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        // Get list of event quizzes
        $eventQuizzes = Quiz::where('is_daily_quiz', 0)->orderBy('created_at', 'desc')->get();
        
        $eventId = $request->input('event_id');
        if (!$eventId && $eventQuizzes->count() > 0) {
            $eventId = $eventQuizzes->first()->id;
        }

        $fullLeaderboard = $this->getEventLeaderboardData($eventId);

        $locations = $fullLeaderboard->pluck('location')
            ->filter(fn($loc) => $loc !== 'Tidak Diketahui')
            ->unique()
            ->sort()
            ->values();

        $filteredLeaderboard = $this->applyFilters($request, $fullLeaderboard);

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

        return Inertia::render('Admin/Leaderboard/Event', [
            'leaderboard' => $paginatedLeaderboard,
            'locations' => $locations,
            'eventQuizzes' => $eventQuizzes,
            'filters' => [
                'event_id' => $eventId,
                'search' => $request->input('search'),
                'location' => $request->input('location'),
                'sort_key' => $request->input('sort_key', 'score'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    private function applyFilters(Request $request, $collection)
    {
        $filtered = $collection;

        $search = $request->input('search');
        if ($search) {
            $searchLower = strtolower(trim($search));
            $filtered = $filtered->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['name'] ?? ''), $searchLower) ||
                       str_contains(strtolower($row['position'] ?? ''), $searchLower) ||
                       str_contains(strtolower($row['location'] ?? ''), $searchLower);
            });
        }

        $locationFilter = $request->input('location');
        if ($locationFilter) {
            $filtered = $filtered->filter(function ($row) use ($locationFilter) {
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
            'attendance' => 'attendance',
        ];

        $sortByField = $sortMap[$sortKey] ?? 'total_score';
        
        $filtered = $filtered->sortBy(function ($row) use ($sortByField) {
            return $row[$sortByField];
        }, SORT_REGULAR, $sortDir === 'desc')->values();

        return $filtered;
    }

    public function exportDaily(Request $request)
    {
        if (!$request->user()->isAdmin()) abort(403);

        $month = $request->filled('month') ? $request->input('month') : date('Y-m');
        $fullLeaderboard = $this->getDailyLeaderboardData($month);
        $filteredLeaderboard = $this->applyFilters($request, $fullLeaderboard);

        $filename = 'leaderboard_harian_' . $month . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new LeaderboardExport($filteredLeaderboard, true), $filename);
    }

    public function exportEvent(Request $request)
    {
        if (!$request->user()->isAdmin()) abort(403);

        $eventId = $request->input('event_id');
        $fullLeaderboard = $this->getEventLeaderboardData($eventId);
        $filteredLeaderboard = $this->applyFilters($request, $fullLeaderboard);

        $event = Quiz::find($eventId);
        $eventName = $event ? \Illuminate\Support\Str::slug($event->title) : 'event';
        
        $filename = 'leaderboard_event_' . $eventName . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new LeaderboardExport($filteredLeaderboard, false), $filename);
    }
}
