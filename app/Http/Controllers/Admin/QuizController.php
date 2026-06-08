<?php

namespace App\Http\Controllers\Admin;

use App\Exports\QuizHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Quiz;
use App\Models\Question;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::withCount('questions')->latest()->paginate(10);
        return Inertia::render('Admin/Quiz/Index', [
            'quizzes' => $quizzes
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Quiz/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_daily_quiz' => 'boolean',
            'daily_question_limit' => 'nullable|integer|min:1'
        ]);

        Quiz::create($request->all());
        return redirect()->route('admin.quizzes.index')->with('success', 'Kuis berhasil dibuat.');
    }

    public function edit(Quiz $quiz)
    {
        return Inertia::render('Admin/Quiz/Edit', [
            'quiz' => $quiz
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_daily_quiz' => 'boolean',
            'daily_question_limit' => 'nullable|integer|min:1'
        ]);

        $quiz->update($request->all());
        return redirect()->route('admin.quizzes.index')->with('success', 'Kuis berhasil diperbarui.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Kuis berhasil dihapus.');
    }

    public function show(Request $request, Quiz $quiz)
    {
        // Untuk Kuis Harian, tidak perlu select soal manual
        if ($quiz->is_daily_quiz) {
            return redirect()->route('admin.quizzes.index')->with('error', 'Kuis Harian mengambil soal acak dari Bank Soal, tidak perlu dikelola secara manual.');
        }

        // Ambil ID soal yang sudah terhubung
        $attachedQuestionIds = $quiz->questions()->pluck('questions.id')->toArray();

        // Query Master Bank Soal
        $query = Question::with('categories');
        
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('category_question.category_id', $request->category_id);
            });
        }
        
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $bankQuestions = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Quiz/Show', [
            'quiz' => $quiz,
            'bankQuestions' => $bankQuestions,
            'attachedQuestionIds' => $attachedQuestionIds,
            'categories' => \App\Models\Category::all(),
            'filters' => $request->only(['category_id', 'risk_level']),
        ]);
    }

    public function attachQuestion(Request $request, Quiz $quiz)
    {
        $request->validate(['question_id' => 'required|exists:questions,id']);
        $quiz->questions()->syncWithoutDetaching([$request->question_id]);
        return redirect()->back()->with('success', 'Soal ditambahkan ke kuis.');
    }

    public function detachQuestion(Request $request, Quiz $quiz)
    {
        $request->validate(['question_id' => 'required|exists:questions,id']);
        $quiz->questions()->detach($request->question_id);
        return redirect()->back()->with('success', 'Soal dihapus dari kuis.');
    }

    public function history(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $search = $request->input('search');
        $month = $request->input('month');
        $sortField = $request->input('sort_field', 'tanggal');
        $sortDirection = $request->input('sort_direction', 'desc');

        $query = QuizAttempt::query()
            ->select('quiz_attempts.*')
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->leftJoin('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->with(['user', 'quiz']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                  ->orWhere('users.nip', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('month')) {
            $query->where('quiz_attempts.month_year', $month);
        }

        $sortMap = [
            'petugas' => 'users.name',
            'nip' => 'users.nip',
            'tanggal' => 'quiz_attempts.created_at',
            'kuis' => 'quizzes.title',
            'score' => 'quiz_attempts.score',
            'correct_answers' => 'quiz_attempts.correct_answers',
            'durasi' => 'quiz_attempts.time_ms',
        ];

        $orderColumn = $sortMap[$sortField] ?? 'quiz_attempts.created_at';
        $orderDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($orderColumn, $orderDirection);

        $history = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Quiz/History', [
            'history' => $history,
            'filters' => [
                'search' => $search,
                'month' => $month,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }

    public function exportHistory(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            abort(403);
        }

        $search = $request->input('search');
        $month = $request->input('month');
        $sortField = $request->input('sort_field', 'tanggal');
        $sortDirection = $request->input('sort_direction', 'desc');

        $filename = 'riwayat-kuis';
        if ($month) {
            $filename .= '-' . $month;
        }
        $filename .= '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new QuizHistoryExport($search, $month, $sortField, $sortDirection),
            $filename
        );
    }
}
