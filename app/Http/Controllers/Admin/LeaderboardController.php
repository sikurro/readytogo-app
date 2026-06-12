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
        $attempts = QuizAttempt::with(['user', 'quiz'])
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
                'location' => $user->location ?: 'Tidak Diketahui',
                'total_score' => $totalScore,
                'total_questions' => $totalQuestions,
                'total_correct' => $totalCorrect,
                'total_wrong' => $totalWrong,
                'percentage_correct' => $percentageCorrect,
            ];
        })
        ->filter()
        ->sortByDesc('total_score')
        ->values();

        return $leaderboard;
    }

    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $month = $request->filled('month') ? $request->input('month') : date('Y-m');

        $leaderboard = $this->getLeaderboardData($month);

        // Bar Chart Data: Group by location
        $barChartData = $leaderboard->groupBy('location')->map(function ($items, $location) {
            return [
                'location' => $location,
                'correct' => $items->sum('total_correct'),
                'wrong' => $items->sum('total_wrong'),
            ];
        })->values();

        // Pie Chart Data: Overall correct vs wrong
        $totalCorrectAll = $leaderboard->sum('total_correct');
        $totalWrongAll = $leaderboard->sum('total_wrong');
        $pieChartData = [
            'correct' => $totalCorrectAll,
            'wrong' => $totalWrongAll,
        ];

        return Inertia::render('Admin/Leaderboard/Index', [
            'leaderboard' => $leaderboard,
            'barChartData' => $barChartData,
            'pieChartData' => $pieChartData,
            'filters' => [
                'month' => $month,
            ],
        ]);
    }

    public function export(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $month = $request->filled('month') ? $request->input('month') : date('Y-m');

        $leaderboard = $this->getLeaderboardData($month);

        $filename = 'leaderboard';
        if ($month) {
            $filename .= '-' . $month;
        }
        $filename .= '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new LeaderboardExport($leaderboard),
            $filename
        );
    }
}
