<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FatigueCheck;
use Inertia\Inertia;
use Carbon\Carbon;

class FatigueCheckController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status'); // 'fit' or 'unfit'
        $date = $request->query('date'); // Y-m-d

        $query = FatigueCheck::with(['user.location'])
            ->latest();

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_fit', $status === 'fit');
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // Paginate results
        $fatigueChecks = $query->paginate(15)->withQueryString();

        // Calculate summary statistics
        $today = Carbon::today();
        $totalToday = FatigueCheck::whereDate('created_at', $today)->count();
        $fitToday = FatigueCheck::whereDate('created_at', $today)->where('is_fit', true)->count();
        $unfitToday = FatigueCheck::whereDate('created_at', $today)->where('is_fit', false)->count();
        
        $avgReactionTimeToday = FatigueCheck::whereDate('created_at', $today)
            ->whereNotNull('reaction_time_ms')
            ->avg('reaction_time_ms') ?? 0;

        return Inertia::render('Admin/FatigueCheck/Index', [
            'fatigueChecks' => $fatigueChecks,
            'filters' => $request->only(['search', 'status', 'date']),
            'summary' => [
                'total_today' => $totalToday,
                'fit_today' => $fitToday,
                'unfit_today' => $unfitToday,
                'avg_reaction_time_today' => round($avgReactionTimeToday, 1),
            ]
        ]);
    }
}
