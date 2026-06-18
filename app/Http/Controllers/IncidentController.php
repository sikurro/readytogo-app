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
}
