<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Quiz;

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
        return Inertia::render('Admin/Dashboard');
    }
}
