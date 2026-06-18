<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Incident;
use Inertia\Inertia;

class IncidentController extends Controller
{
    /**
     * Display a list of incidents reported by the authenticated user.
     */
    public function index(Request $request)
    {
        $incidents = Incident::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Incidents/Index', [
            'incidents' => $incidents,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new incident report.
     */
    public function create()
    {
        return Inertia::render('Incidents/Create');
    }

    /**
     * Store a newly created incident report in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:unsafe_condition,unsafe_act,near_miss,positive_observation',
            'severity' => 'required|in:low,medium,high',
            'description' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('incidents', 'public');
        }

        $request->user()->incidents()->create([
            'category' => $validated['category'],
            'severity' => $validated['severity'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'image_path' => $imagePath,
            'status' => 'open',
        ]);

        return redirect()->route('incidents.index')->with('success', 'Laporan insiden berhasil dikirim!');
    }

    /**
     * Display a listing of all incidents for the admin panel.
     */
    public function adminIndex(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $query = Incident::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // All incidents for summary widget and global map markers (unfiltered)
        $allIncidents = Incident::with('user')->latest()->get();

        // Paginated incidents for the table list
        $incidents = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Incidents/Index', [
            'incidents' => $incidents,
            'allIncidents' => $allIncidents,
            'filters' => $request->only(['status', 'category', 'severity']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Update the status of the specified incident report.
     */
    public function updateStatus(Request $request, Incident $incident)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,investigating,closed',
            'admin_feedback' => 'required_if:status,closed|nullable|string',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'admin_feedback' => $validated['admin_feedback'] ?? null,
        ];

        if ($validated['status'] === 'closed') {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = $request->user()->id;
        }

        $incident->update($updateData);

        if ($validated['status'] === 'closed') {
            $incident->user->notify(new \App\Notifications\IncidentResolved($incident));
        }

        return redirect()->route('admin.incidents.index')->with('success', 'Status laporan berhasil diperbarui!');
    }

    /**
     * Mark all unread notifications of the user as read.
     */
    public function markNotificationsAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back();
    }

    /**
     * Display the Incident Dashboard.
     */
    public function dashboard(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $totalMonthly = Incident::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $priorityCount = Incident::where('status', 'open')
            ->where('severity', 'high')
            ->count();

        $totalIncidents = Incident::count();
        $resolvedIncidents = Incident::where('status', 'closed')->count();
        $resolutionRate = $totalIncidents > 0 ? round(($resolvedIncidents / $totalIncidents) * 100, 1) : 0;

        $positiveObservations = Incident::where('category', 'positive_observation')->count();

        // 6 Months Trend
        $trendLabels = [];
        $trendCounts = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $trendLabels[] = $monthNames[$month->month - 1] . ' ' . $month->year;
            $trendCounts[] = Incident::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        // Composition
        $composition = [
            'unsafe_condition' => Incident::where('category', 'unsafe_condition')->count(),
            'unsafe_act' => Incident::where('category', 'unsafe_act')->count(),
            'near_miss' => Incident::where('category', 'near_miss')->count(),
            'positive_observation' => Incident::where('category', 'positive_observation')->count(),
        ];

        // Map Incidents
        $mapIncidents = Incident::with('user')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->get();

        // Top Reporters
        $topReporters = \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'Petugas');
            })
            ->withCount('incidents')
            ->orderByDesc('incidents_count')
            ->take(5)
            ->get();

        // Critical Incidents
        $criticalIncidents = Incident::with('user')
            ->where('status', 'open')
            ->where(function($q) {
                $q->where('severity', 'high')
                  ->orWhere('category', 'near_miss');
            })
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Incidents/Dashboard', [
            'stats' => [
                'totalMonthly' => $totalMonthly,
                'priorityCount' => $priorityCount,
                'resolutionRate' => $resolutionRate,
                'positiveObservations' => $positiveObservations,
            ],
            'trend' => [
                'labels' => $trendLabels,
                'data' => $trendCounts,
            ],
            'composition' => $composition,
            'mapIncidents' => $mapIncidents,
            'topReporters' => $topReporters,
            'criticalIncidents' => $criticalIncidents,
        ]);
    }

    /**
     * Export admin incidents to Excel.
     */
    public function adminExport(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $filters = $request->only(['status', 'category', 'severity']);
        $filename = 'laporan-insiden-' . now()->format('Y-m-d') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\IncidentExport($filters), 
            $filename
        );
    }
}
