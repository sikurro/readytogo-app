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
        $status = $request->query('status'); // 'fit', 'unfit', or 'belum_check'
        $date = $request->query('date', Carbon::today()->format('Y-m-d')); // Y-m-d

        $targetDate = $date ? Carbon::parse($date) : Carbon::today();

        if ($status === 'belum_check') {
            $userQuery = \App\Models\User::whereHas('role', function ($q) {
                $q->where('name', 'Petugas');
            })->whereDoesntHave('fatigueChecks', function ($q) use ($targetDate, $date) {
                if ($date && strlen($date) === 7) {
                    $q->whereYear('created_at', substr($date, 0, 4))
                      ->whereMonth('created_at', substr($date, 5, 2));
                } else {
                    $q->whereDate('created_at', $targetDate);
                }
            })->with(['location']);

            if ($search) {
                $userQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Paginate results
            $paginatedUsers = $userQuery->paginate(15)->withQueryString();

            // Transform data to fit the frontend FatigueCheck row structure
            $transformedData = collect($paginatedUsers->items())->map(function ($user) {
                return [
                    'id' => 'user-' . $user->id,
                    'user' => [
                        'name' => $user->name,
                        'nip' => $user->nip,
                        'location' => $user->location,
                    ],
                    'created_at' => null,
                    'questionnaire_status' => null,
                    'reaction_time_ms' => null,
                    'is_fit' => null,
                    'is_belum_tes' => true,
                ];
            });

            $fatigueChecks = new \Illuminate\Pagination\LengthAwarePaginator(
                $transformedData,
                $paginatedUsers->total(),
                $paginatedUsers->perPage(),
                $paginatedUsers->currentPage(),
                [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } else {
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
                if (strlen($date) === 7) { // Format: YYYY-MM
                    $query->whereYear('created_at', substr($date, 0, 4))
                          ->whereMonth('created_at', substr($date, 5, 2));
                } else {
                    $query->whereDate('created_at', $date);
                }
            }

            // Paginate results
            $fatigueChecks = $query->paginate(15)->withQueryString();
        }

        // Calculate summary statistics
        if ($date && strlen($date) === 7) {
            $totalToday = FatigueCheck::whereYear('created_at', substr($date, 0, 4))
                ->whereMonth('created_at', substr($date, 5, 2))
                ->count();
            $fitToday = FatigueCheck::whereYear('created_at', substr($date, 0, 4))
                ->whereMonth('created_at', substr($date, 5, 2))
                ->where('is_fit', true)
                ->count();
            $unfitToday = FatigueCheck::whereYear('created_at', substr($date, 0, 4))
                ->whereMonth('created_at', substr($date, 5, 2))
                ->where('is_fit', false)
                ->count();
            
            $avgReactionTimeToday = FatigueCheck::whereYear('created_at', substr($date, 0, 4))
                ->whereMonth('created_at', substr($date, 5, 2))
                ->whereNotNull('reaction_time_ms')
                ->avg('reaction_time_ms') ?? 0;

            $notTestedCount = \App\Models\User::whereHas('role', function ($q) {
                $q->where('name', 'Petugas');
            })->whereDoesntHave('fatigueChecks', function ($q) use ($date) {
                $q->whereYear('created_at', substr($date, 0, 4))
                  ->whereMonth('created_at', substr($date, 5, 2));
            })->count();
        } else {
            $totalToday = FatigueCheck::whereDate('created_at', $targetDate)->count();
            $fitToday = FatigueCheck::whereDate('created_at', $targetDate)->where('is_fit', true)->count();
            $unfitToday = FatigueCheck::whereDate('created_at', $targetDate)->where('is_fit', false)->count();
            
            $avgReactionTimeToday = FatigueCheck::whereDate('created_at', $targetDate)
                ->whereNotNull('reaction_time_ms')
                ->avg('reaction_time_ms') ?? 0;

            $notTestedCount = \App\Models\User::whereHas('role', function ($q) {
                $q->where('name', 'Petugas');
            })->whereDoesntHave('fatigueChecks', function ($q) use ($targetDate) {
                $q->whereDate('created_at', $targetDate);
            })->count();
        }

        return Inertia::render('Admin/FatigueCheck/Index', [
            'fatigueChecks' => $fatigueChecks,
            'filters' => $request->only(['search', 'status', 'date']),
            'summary' => [
                'total_today' => $totalToday,
                'fit_today' => $fitToday,
                'unfit_today' => $unfitToday,
                'belum_check' => $notTestedCount,
                'avg_reaction_time_today' => round($avgReactionTimeToday, 1),
            ],
            'notTestedUsers' => [], // keep it for backward compatibility/prop validation
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'date']);
        $filename = 'fatigue-checks-' . now()->format('Y-m-d') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\FatigueCheckExport($filters), 
            $filename
        );
    }
}
