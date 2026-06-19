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

        $incident = $request->user()->incidents()->create([
            'category' => $validated['category'],
            'severity' => $validated['severity'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'image_path' => $imagePath,
            'status' => 'open',
        ]);

        $admins = \App\Models\User::whereHas('role', function($q) {
            $q->where('name', 'Admin');
        })->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewIncidentReported($incident));

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

        // Summary stats (aggregate, bukan load seluruh data)
        $summaryStats = [
            'total' => Incident::count(),
            'open' => Incident::where('status', 'open')->count(),
            'investigating' => Incident::where('status', 'investigating')->count(),
            'closed' => Incident::where('status', 'closed')->count(),
        ];

        // Map markers: hanya field yg dibutuhkan + hanya yang punya koordinat
        $mapIncidents = Incident::select('id', 'category', 'severity', 'status', 'latitude', 'longitude', 'created_at', 'user_id')
            ->with('user:id,name')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->limit(200)
            ->get();

        // Paginated incidents for the table list
        $incidents = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Incidents/Index', [
            'incidents' => $incidents,
            'summaryStats' => $summaryStats,
            'mapIncidents' => $mapIncidents,
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

        if (in_array($validated['status'], ['investigating', 'closed'])) {
            $incident->user->notify(new \App\Notifications\IncidentStatusUpdated($incident, $validated['status']));
        }

        return redirect()->route('admin.incidents.index')->with('success', 'Status laporan berhasil diperbarui!');
    }

    /**
     * Get unread notifications for lightweight polling.
     */
    public function getUnreadNotifications(Request $request)
    {
        return response()->json($request->user()->unreadNotifications);
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
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $startMonth = now()->subMonths(5)->startOfMonth();

        $trendRaw = Incident::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as count")
            ->where('created_at', '>=', $startMonth)
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->pluck('count', 'month_key');

        $trendLabels = [];
        $trendCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $key = $m->format('Y-m');
            $trendLabels[] = $monthNames[$m->month - 1] . ' ' . $m->year;
            $trendCounts[] = $trendRaw->get($key, 0);
        }

        // Composition
        $compositionRaw = Incident::selectRaw("category, COUNT(*) as count")
            ->groupBy('category')
            ->pluck('count', 'category');

        $composition = [
            'unsafe_condition' => $compositionRaw->get('unsafe_condition', 0),
            'unsafe_act' => $compositionRaw->get('unsafe_act', 0),
            'near_miss' => $compositionRaw->get('near_miss', 0),
            'positive_observation' => $compositionRaw->get('positive_observation', 0),
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
