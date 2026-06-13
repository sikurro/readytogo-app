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
        $quizzes = Quiz::where('is_active', true)->where('is_daily_quiz', true)->get();
        $userId = Auth::id();
        
        $quizzesWithStatus = $quizzes->map(function ($quiz) use ($userId) {
            $hasPlayedToday = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quiz->id)
                ->whereDate('created_at', today())
                ->exists();
                
            $quiz->has_played_today = $hasPlayedToday;
            return $quiz;
        });

        return Inertia::render('Petugas/Quiz/Index', [
            'quizzes' => $quizzesWithStatus,
        ]);
    }

    public function play(Request $request, Quiz $quiz)
    {
        if (!$quiz->is_active) {
            return redirect()->route('quiz.index')->with('error', 'Kuis tidak tersedia.');
        }

        if (!$quiz->is_daily_quiz) {
            $now = now();
            if ($quiz->start_time && $now->lt($quiz->start_time)) {
                return redirect()->route('dashboard')->with('error', 'Event kuis belum dimulai.');
            }
            if ($quiz->end_time && $now->gt($quiz->end_time)) {
                return redirect()->route('dashboard')->with('error', 'Event kuis sudah berakhir.');
            }
        }

        $isDemo = $request->query('demo') === '1';

        if (!$isDemo) {
            $hasPlayedToday = QuizAttempt::where('user_id', Auth::id())
                ->where('quiz_id', $quiz->id)
                ->whereDate('created_at', today())
                ->exists();

            if ($hasPlayedToday) {
                return redirect()->route('quiz.index')->with('error', 'Anda sudah bermain hari ini.');
            }
        }

        if ($quiz->is_daily_quiz) {
            // Kuis Harian: Ambil dari Master Bank Soal
            $limit = $quiz->daily_question_limit ?: 10;
            $questions = \App\Models\Question::inRandomOrder()->limit($limit)->with(['answers' => function ($q) {
                $q->inRandomOrder();
            }])->get();
            $quiz->setRelation('questions', $questions);
        } else {
            // Event Quiz: Ambil dari Pivot Table
            $quiz->load(['questions' => function ($query) {
                $query->inRandomOrder()->with(['answers' => function ($q) {
                    $q->inRandomOrder();
                }]);
            }]);
        }

        return Inertia::render('Petugas/Quiz/Play', [
            'quiz' => $quiz,
            'isDemo' => $isDemo,
        ]);
    }

    public function storeAttempt(Request $request, Quiz $quiz)
    {
        $request->validate([
            'score' => 'required|integer',
            'correct_answers' => 'required|integer',
            'time_ms' => 'required|integer',
            'is_demo' => 'nullable|boolean',
        ]);

        if (!$quiz->is_active) {
            return redirect()->route('quiz.index')->with('error', 'Kuis tidak ditemukan.');
        }

        if ($request->input('is_demo')) {
            return redirect()->route('quiz.demo-summary', [
                'quiz' => $quiz->id,
                'score' => $request->score,
                'correct_answers' => $request->correct_answers,
                'time_ms' => $request->time_ms
            ]);
        }

        $hasPlayedToday = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quiz->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($hasPlayedToday) {
            return redirect()->route('quiz.index')->with('error', 'Anda sudah bermain hari ini.');
        }

        $attempt = QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'score' => $request->score,
            'correct_answers' => $request->correct_answers,
            'time_ms' => $request->time_ms,
            'month_year' => date('Y-m'),
        ]);

        return redirect()->route('quiz.summary', $attempt->id);
    }

    public function summary(QuizAttempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        $attempt->load('quiz');
        
        $totalQuestions = $attempt->quiz->is_daily_quiz 
            ? ($attempt->quiz->daily_question_limit ?: 10) 
            : $attempt->quiz->questions()->count();
            
        $incorrectAnswers = $totalQuestions - $attempt->correct_answers;

        return Inertia::render('Petugas/Quiz/Summary', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'total_questions' => $totalQuestions,
            'incorrect_answers' => max(0, $incorrectAnswers),
            'is_demo' => false
        ]);
    }

    public function demoSummary(Request $request, Quiz $quiz)
    {
        $score = $request->query('score', 0);
        $correctAnswers = $request->query('correct_answers', 0);
        $timeMs = $request->query('time_ms', 0);

        $totalQuestions = $quiz->is_daily_quiz 
            ? ($quiz->daily_question_limit ?: 10) 
            : $quiz->questions()->count();
            
        $incorrectAnswers = $totalQuestions - $correctAnswers;

        $attempt = [
            'score' => (int)$score,
            'correct_answers' => (int)$correctAnswers,
            'time_ms' => (int)$timeMs,
        ];

        return Inertia::render('Petugas/Quiz/Summary', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'total_questions' => $totalQuestions,
            'incorrect_answers' => max(0, $incorrectAnswers),
            'is_demo' => true
        ]);
    }

    public function leaderboard()
    {
        $currentMonth = date('Y-m');

        // Mengambil semua user yang pernah melakukan percobaan bulan ini
        // dan mengakumulasi skor dan durasi mereka.
        $leaderboard = QuizAttempt::with('user')
            ->whereHas('quiz', function ($q) {
                $q->where('is_daily_quiz', 1);
            })
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
