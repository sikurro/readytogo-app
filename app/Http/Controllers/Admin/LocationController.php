<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Location;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $locations = Location::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Location/Index', [
            'locations' => $locations,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name'
        ]);

        Location::create($validated);

        return redirect()->back()->with('success', 'Lokasi/Unit Kerja berhasil ditambahkan.');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id
        ]);

        $location->update($validated);

        return redirect()->back()->with('success', 'Lokasi/Unit Kerja berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->back()->with('success', 'Lokasi/Unit Kerja berhasil dihapus.');
    }
}
