<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function index()
    {
        $quiz = Quiz::where('is_active', true)->first();
        
        $hasPlayedToday = false;
        if ($quiz) {
            $hasPlayedToday = QuizAttempt::where('user_id', Auth::id())
                ->where('quiz_id', $quiz->id)
                ->whereDate('created_at', today())
                ->exists();
        }

        return Inertia::render('Petugas/Quiz/Index', [
            'quiz' => $quiz,
            'hasPlayedToday' => $hasPlayedToday,
        ]);
    }

    public function play()
    {
        $quiz = Quiz::where('is_active', true)->first();

        if (!$quiz) {
            return redirect()->route('quiz.index')->with('error', 'Kuis tidak tersedia.');
        }

        $hasPlayedToday = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quiz->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($hasPlayedToday) {
            return redirect()->route('quiz.index')->with('error', 'Anda sudah bermain hari ini.');
        }

        // Load questions with answers in random order
        $quiz->load(['questions' => function ($query) {
            $query->inRandomOrder()->with(['answers' => function ($q) {
                $q->inRandomOrder();
            }]);
        }]);

        return Inertia::render('Petugas/Quiz/Play', [
            'quiz' => $quiz,
        ]);
    }

    public function storeAttempt(Request $request)
    {
        $request->validate([
            'score' => 'required|integer',
            'correct_answers' => 'required|integer',
            'time_ms' => 'required|integer',
        ]);

        $quiz = Quiz::where('is_active', true)->first();

        if (!$quiz) {
            return redirect()->route('quiz.index')->with('error', 'Kuis tidak ditemukan.');
        }

        $hasPlayedToday = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quiz->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($hasPlayedToday) {
            return redirect()->route('quiz.index')->with('error', 'Anda sudah bermain hari ini.');
        }

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'score' => $request->score,
            'correct_answers' => $request->correct_answers,
            'time_ms' => $request->time_ms,
            'month_year' => date('Y-m'),
        ]);

        return redirect()->route('quiz.leaderboard')->with('success', 'Skor berhasil disimpan!');
    }

    public function leaderboard()
    {
        $currentMonth = date('Y-m');

        // Mengambil semua user yang pernah melakukan percobaan bulan ini
        // dan mengakumulasi skor dan durasi mereka.
        $leaderboard = QuizAttempt::with('user')
            ->where('month_year', $currentMonth)
            ->selectRaw('user_id, SUM(score) as total_score, SUM(correct_answers) as total_correct, MAX(created_at) as last_played, SUM(time_ms) as total_time')
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->orderBy('total_time')
            ->get();

        return Inertia::render('Petugas/Quiz/Leaderboard', [
            'leaderboard' => $leaderboard,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function history()
    {
        $currentMonth = date('Y-m');
        $history = QuizAttempt::with('quiz')
            ->where('user_id', Auth::id())
            ->where('month_year', $currentMonth)
            ->latest()
            ->get();

        return Inertia::render('Petugas/Quiz/History', [
            'history' => $history,
            'currentMonth' => $currentMonth,
        ]);
    }
}
