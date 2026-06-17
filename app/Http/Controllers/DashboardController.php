<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Quiz;
use App\Models\User;
use App\Models\FatigueCheck;
use App\Models\QuizAttempt;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the Petugas dashboard.
     */
    public function index(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $now = now();
        $activeEventQuiz = Quiz::where('is_active', true)
            ->where('is_daily_quiz', false)
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
                })->where(function($q) use ($now) {
                    $q->whereNull('end_time')->orWhere('end_time', '>=', $now);
                });
            })
            ->first();

        $hasAttemptedEventQuiz = $activeEventQuiz
            ? $activeEventQuiz->attempts()->where('user_id', $request->user()->id)->exists()
            : false;

        $userTimezone = $request->cookie('user_timezone', 'Asia/Jakarta');
        $appTimezone = config('app.timezone', 'Asia/Jakarta');

        $todayStart = now($userTimezone)->startOfDay()->setTimezone($appTimezone);
        $todayEnd = now($userTimezone)->endOfDay()->setTimezone($appTimezone);

        $latestFatigueCheckToday = $request->user()->fatigueChecks()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->latest()
            ->first();

        $statusBugarHariIni = $latestFatigueCheckToday ? $latestFatigueCheckToday->is_fit : null;

        $hasCompletedDailyQuizToday = $request->user()->quizAttempts()
            ->whereDate('created_at', today())
            ->whereHas('quiz', function($query) {
                $query->where('is_daily_quiz', true);
            })
            ->exists();

        $tips = [
            "Gunakan rompi keselamatan reflektif (high-visibility vest) setiap saat selama berada di area operasional dermaga agar terlihat oleh operator alat berat.",
            "Selalu patuhi batas kecepatan kendaraan maksimum 20 km/jam saat mengemudi di dalam area operasional pelabuhan.",
            "Jangan pernah berjalan atau berdiri di bawah muatan kargo yang sedang diangkat oleh crane atau spreader container.",
            "Gunakan sepatu pelindung (safety shoes) baja untuk melindungi kaki dari risiko tertimpa kontainer atau benda berat di area stacking yard.",
            "Pastikan tali tambat kapal (mooring lines) bebas dari puntiran dan jangan pernah berdiri di area sentakan balik (snap-back zone).",
            "Laporkan segera jika melihat ceceran oli atau kerusakan struktur dermaga kepada petugas K3 pelabuhan untuk mencegah kecelakaan terpeleset.",
            "Gunakan pelindung telinga (earplug/earmuff) saat bekerja dekat area dengan intensitas kebisingan tinggi seperti genset kapal atau ruang mesin crane."
        ];
        $safetyTip = \Illuminate\Support\Arr::random($tips);

        return Inertia::render('Petugas/Dashboard', [
            'activeEventQuiz' => $activeEventQuiz,
            'statusBugarHariIni' => $statusBugarHariIni,
            'hasAttemptedEventQuiz' => $hasAttemptedEventQuiz,
            'safetyTip' => $safetyTip,
            'hasCompletedDailyQuizToday' => $hasCompletedDailyQuizToday,
        ]);
    }

    /**
     * Display the Admin dashboard.
     */
    public function adminIndex(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        // Total user (petugas)
        $totalUsers = User::whereHas('role', function ($q) {
            $q->where('name', 'Petugas');
        })->count();

        // Latest fatigue checks today for each user
        $latestChecksToday = FatigueCheck::whereDate('created_at', today())
            ->whereIn('id', function ($query) {
                $query->selectRaw('max(id)')
                    ->from('fatigue_checks')
                    ->whereDate('created_at', today())
                    ->groupBy('user_id');
            })->get();

        $testedFatigueToday = $latestChecksToday->count();
        $fitToday = $latestChecksToday->where('is_fit', true)->count();
        $unfitToday = $latestChecksToday->where('is_fit', false)->count();
        $notTestedFatigueToday = max(0, $totalUsers - $testedFatigueToday);

        // Quiz attempts today (distinct users)
        $quizTakenToday = QuizAttempt::whereDate('created_at', today())
            ->whereHas('quiz', function($q) {
                $q->where('is_daily_quiz', true);
            })
            ->distinct('user_id')
            ->count('user_id');
        $quizNotTakenToday = max(0, $totalUsers - $quizTakenToday);

        // Top 10 monthly leaderboard
        $currentMonth = date('Y-m');
        $top10Leaderboard = User::whereHas('role', function($q) {
                $q->where('name', 'Petugas');
            })
            ->with(['location'])
            ->withSum(['quizAttempts' => function($q) use ($currentMonth) {
                $q->where('month_year', $currentMonth)
                  ->whereHas('quiz', function($qQuiz) {
                      $qQuiz->where('is_daily_quiz', true);
                  });
            }], 'score')
            ->whereHas('quizAttempts', function($q) use ($currentMonth) {
                $q->where('month_year', $currentMonth)
                  ->whereHas('quiz', function($qQuiz) {
                      $qQuiz->where('is_daily_quiz', true);
                  });
            })
            ->orderByDesc('quiz_attempts_sum_score')
            ->take(10)
            ->get();

        // Quiz attempts 30 days (for the 30-day pie chart)
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();
        $attempts30Days = QuizAttempt::with(['quiz'])
            ->whereHas('quiz', function ($q) {
                $q->where('is_daily_quiz', 1);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $quiz30DaysCorrect = $attempts30Days->sum('correct_answers');
        $quiz30DaysQuestions = $attempts30Days->sum(function ($attempt) {
            if (!$attempt->quiz) return $attempt->correct_answers;
            return $attempt->quiz->daily_question_limit ?: 10;
        });
        $quiz30DaysWrong = max(0, $quiz30DaysQuestions - $quiz30DaysCorrect);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'testedFatigueToday' => $testedFatigueToday,
                'fitToday' => $fitToday,
                'unfitToday' => $unfitToday,
                'notTestedFatigueToday' => $notTestedFatigueToday,
                'quizTakenToday' => $quizTakenToday,
                'quizNotTakenToday' => $quizNotTakenToday,
                'quiz30DaysCorrect' => $quiz30DaysCorrect,
                'quiz30DaysWrong' => $quiz30DaysWrong,
            ],
            'top10Leaderboard' => $top10Leaderboard
        ]);
    }

    /**
     * Get chart data for admin dashboard.
     */
    public function chartData(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // --- Fatigue Today Summary ---
        $totalUsers = User::whereHas('role', function ($q) {
            $q->where('name', 'Petugas');
        })->count();

        $latestChecksToday = FatigueCheck::whereDate('created_at', today())
            ->whereIn('id', function ($query) {
                $query->selectRaw('max(id)')
                    ->from('fatigue_checks')
                    ->whereDate('created_at', today())
                    ->groupBy('user_id');
            })->get();

        $testedFatigueToday = $latestChecksToday->count();
        $fitToday = $latestChecksToday->where('is_fit', true)->count();
        $unfitToday = $latestChecksToday->where('is_fit', false)->count();
        $notTestedFatigueToday = max(0, $totalUsers - $testedFatigueToday);

        // --- Fatigue Monthly Chart Data ---
        $daysInMonth = now()->daysInMonth;
        $fatigueChecksThisMonth = FatigueCheck::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('created_at', 'asc')
            ->get();

        $monthlyLabels = [];
        $monthlyFit = [];
        $monthlyUnfit = [];
        $monthlyTotal = [];
        $dailyTestedUsers = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
            $monthlyLabels[] = $dayStr;
            $monthlyFit[$dayStr] = 0;
            $monthlyUnfit[$dayStr] = 0;
            $monthlyTotal[$dayStr] = 0;
            $dailyTestedUsers[$dayStr] = [];
        }

        foreach ($fatigueChecksThisMonth as $check) {
            $day = $check->created_at->format('d');
            if (isset($monthlyTotal[$day])) {
                if ($check->is_fit) {
                    $monthlyFit[$day]++;
                } else {
                    $monthlyUnfit[$day]++;
                }
                $monthlyTotal[$day]++;
                $dailyTestedUsers[$day][$check->user_id] = true;
            }
        }

        $monthlyNotTested = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
            $testedCount = count($dailyTestedUsers[$dayStr]);
            $monthlyNotTested[$dayStr] = max(0, $totalUsers - $testedCount);
        }

        // --- Quiz Trend (Last 30 Days) ---
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $attempts30Days = QuizAttempt::with(['quiz'])
            ->whereHas('quiz', function ($q) {
                $q->where('is_daily_quiz', 1);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $attemptsByDate = $attempts30Days->groupBy(function ($attempt) {
            return $attempt->created_at->format('Y-m-d');
        });

        $quizLabels = [];
        $quizAvgScore = [];
        $quizAvgAccuracy = [];
        $quizTotalAttempts = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dateLabel = $date->format('d-m');
            $quizLabels[] = $dateLabel;

            $dateAttempts = $attemptsByDate->get($dateStr);

            if ($dateAttempts && $dateAttempts->count() > 0) {
                $avgScore = $dateAttempts->avg('score');
                $totalCorrect = $dateAttempts->sum('correct_answers');
                $totalQuestions = $dateAttempts->sum(function ($attempt) {
                    if (!$attempt->quiz) return $attempt->correct_answers;
                    return $attempt->quiz->daily_question_limit ?: 10;
                });
                $avgAccuracy = $totalQuestions > 0 
                    ? round(($totalCorrect / $totalQuestions) * 100, 2)
                    : 0;
                $quizAvgScore[] = round($avgScore, 2);
                $quizAvgAccuracy[] = $avgAccuracy;
                $quizTotalAttempts[] = $dateAttempts->count();
            } else {
                $quizAvgScore[] = 0;
                $quizAvgAccuracy[] = 0;
                $quizTotalAttempts[] = 0;
            }
        }

        $totalCorrect30Days = $attempts30Days->sum('correct_answers');
        $totalQuestions30Days = $attempts30Days->sum(function ($attempt) {
            if (!$attempt->quiz) return $attempt->correct_answers;
            return $attempt->quiz->daily_question_limit ?: 10;
        });
        $totalWrong30Days = max(0, $totalQuestions30Days - $totalCorrect30Days);

        // --- Quiz Participation Today ---
        $quizTakenToday = QuizAttempt::whereDate('created_at', today())
            ->whereHas('quiz', function($q) {
                $q->where('is_daily_quiz', true);
            })
            ->distinct('user_id')
            ->count('user_id');
        $quizNotTakenToday = max(0, $totalUsers - $quizTakenToday);

        // --- Top 10 Leaderboard ---
        $currentMonth = date('Y-m');
        $top10Leaderboard = User::whereHas('role', function($q) {
                $q->where('name', 'Petugas');
            })
            ->with(['location'])
            ->withSum(['quizAttempts' => function($q) use ($currentMonth) {
                $q->where('month_year', $currentMonth)
                  ->whereHas('quiz', function($qQuiz) {
                      $qQuiz->where('is_daily_quiz', true);
                  });
            }], 'score')
            ->whereHas('quizAttempts', function($q) use ($currentMonth) {
                $q->where('month_year', $currentMonth)
                  ->whereHas('quiz', function($qQuiz) {
                      $qQuiz->where('is_daily_quiz', true);
                  });
            })
            ->orderByDesc('quiz_attempts_sum_score')
            ->take(10)
            ->get();

        return response()->json([
            'fatigueToday' => [
                'fit' => $fitToday,
                'unfit' => $unfitToday,
                'notTested' => $notTestedFatigueToday
            ],
            'fatigueMonthly' => [
                'labels' => $monthlyLabels,
                'fit' => array_values($monthlyFit),
                'unfit' => array_values($monthlyUnfit),
                'notTested' => array_values($monthlyNotTested),
                'total' => array_values($monthlyTotal),
            ],
            'quizTrend' => [
                'labels' => $quizLabels,
                'avgScore' => $quizAvgScore,
                'avgAccuracy' => $quizAvgAccuracy,
                'totalAttempts' => $quizTotalAttempts,
            ],
            'quiz30Days' => [
                'correct' => $totalCorrect30Days,
                'wrong' => $totalWrong30Days,
            ],
            'quizToday' => [
                'taken' => $quizTakenToday,
                'notTaken' => $quizNotTakenToday,
            ],
            'top10Leaderboard' => $top10Leaderboard
        ]);
    }

    /**
     * Get user details for fatigue status today.
     */
    public function fatigueDetails(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $status = $request->query('status');

        $petugasUsers = User::whereHas('role', function ($q) {
            $q->where('name', 'Petugas');
        })->with('location')->get();

        $latestChecksToday = FatigueCheck::whereDate('created_at', today())
            ->whereIn('id', function ($query) {
                $query->selectRaw('max(id)')
                    ->from('fatigue_checks')
                    ->whereDate('created_at', today())
                    ->groupBy('user_id');
            })->get()->keyBy('user_id');

        $result = [];

        foreach ($petugasUsers as $user) {
            $check = $latestChecksToday->get($user->id);
            $isTested = $check !== null;
            $isFit = $isTested && $check->is_fit;
            $isUnfit = $isTested && !$check->is_fit;

            $includeUser = false;
            if ($status === 'total') $includeUser = true;
            elseif ($status === 'tested' && $isTested) $includeUser = true;
            elseif ($status === 'fit' && $isFit) $includeUser = true;
            elseif ($status === 'unfit' && $isUnfit) $includeUser = true;
            elseif ($status === 'not_tested' && !$isTested) $includeUser = true;

            if ($includeUser) {
                $result[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nip' => $user->nip ?? '-',
                    'location' => $user->location ? $user->location->name : '-',
                    'time' => $isTested ? $check->created_at->format('H:i') : null,
                    'status_label' => $isTested ? ($isFit ? 'Fit' : 'Unfit') : 'Belum Tes',
                ];
            }
        }

        return response()->json($result);
    }
}



