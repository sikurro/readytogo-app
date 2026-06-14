<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FatigueQuestion;
use Inertia\Inertia;

class FatigueQuestionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $questions = FatigueQuestion::when($search, function ($query, $search) {
                $query->where('question_text', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/FatigueQuestion/Index', [
            'questions' => $questions,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|min:5|max:1000',
            'is_active' => 'required|boolean'
        ]);

        FatigueQuestion::create($validated);

        return redirect()->back()->with('success', 'Pertanyaan fatigue berhasil ditambahkan.');
    }

    public function update(Request $request, FatigueQuestion $fatigueQuestion)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|min:5|max:1000',
            'is_active' => 'required|boolean'
        ]);

        $fatigueQuestion->update($validated);

        return redirect()->back()->with('success', 'Pertanyaan fatigue berhasil diperbarui.');
    }

    public function destroy(FatigueQuestion $fatigueQuestion)
    {
        $fatigueQuestion->delete();

        return redirect()->back()->with('success', 'Pertanyaan fatigue berhasil dihapus.');
    }
}
