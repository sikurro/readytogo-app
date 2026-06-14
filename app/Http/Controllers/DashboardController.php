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

        $userTimezone = $request->cookie('user_timezone', 'Asia/Jakarta');

        $todayStart = now($userTimezone)->startOfDay()->setTimezone('UTC');
        $todayEnd = now($userTimezone)->endOfDay()->setTimezone('UTC');

        $latestFatigueCheckToday = $request->user()->fatigueChecks()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->latest()
            ->first();

        $statusBugarHariIni = $latestFatigueCheckToday ? $latestFatigueCheckToday->is_fit : null;

        return Inertia::render('Petugas/Dashboard', [
            'activeEventQuiz' => $activeEventQuiz,
            'statusBugarHariIni' => $statusBugarHariIni
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
