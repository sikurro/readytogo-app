<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
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
}
