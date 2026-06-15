<?php

namespace App\Http\Controllers;

use App\Models\FatigueCheck;
use App\Models\FatigueQuestion;
use App\Models\FatigueCheckAnswer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FatigueCheckController extends Controller
{
    /**
     * Display the Fatigue Hub page.
     */
    public function hub(Request $request)
    {
        $todayCheck = FatigueCheck::where('user_id', $request->user()->id)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        // Calculate stats for the last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $recentChecks = FatigueCheck::where('user_id', $request->user()->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();

        $stats = [
            'avg_reaction_time' => $recentChecks->avg('reaction_time_ms') ? round($recentChecks->avg('reaction_time_ms')) : 0,
            'total_tests' => $recentChecks->count(),
            'total_fit' => $recentChecks->where('is_fit', true)->count(),
        ];
        
        $stats['fit_rate'] = $stats['total_tests'] > 0 
            ? round(($stats['total_fit'] / $stats['total_tests']) * 100) 
            : 0;

        $tips = [
            "Aturan 20-20-20: Setiap 20 menit menatap layar, istirahatkan mata dengan melihat objek sejauh 20 kaki (6 meter) selama 20 detik.",
            "Dehidrasi ringan dapat menurunkan konsentrasi dan memperlambat waktu reaksi. Pastikan Anda minum cukup air hari ini.",
            "Kurang tidur 2 jam dapat menurunkan waktu reaksi setara dengan mengonsumsi 1 gelas alkohol.",
            "Sempatkan berjemur 5-10 menit sebelum mulai bekerja. Cahaya matahari pagi membantu mengatur jam biologis tubuh.",
            "Lakukan peregangan leher dan bahu selama 3 menit setiap beberapa jam untuk mengurangi kekakuan otot dan mencegah kelelahan fisik.",
            "Mengantuk saat bekerja? Lakukan *power nap* (tidur singkat) selama 15-20 menit saat istirahat untuk memulihkan fokus mental.",
        ];
        $dailyTip = \Illuminate\Support\Arr::random($tips);

        return Inertia::render('Fatigue/Hub', [
            'todayCheck' => $todayCheck,
            'stats' => $stats,
            'dailyTip' => $dailyTip,
        ]);
    }

    /**
     * Display the initial questionnaire page.
     */
    public function index()
    {
        $questions = FatigueQuestion::where('is_active', true)->get();
        return Inertia::render('Fatigue/Questionnaire', [
            'questions' => $questions
        ]);
    }

    /**
     * Process the questionnaire submission.
     */
    public function processQuestionnaire(Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
            'answers' => 'required|array'
        ]);

        session(['fatigue_questionnaire' => [
            'status' => $request->status,
            'answers' => $request->answers
        ]]);

        return redirect()->route('fatigue.test');
    }

    /**
     * Display the reaction test page.
     */
    public function test(Request $request)
    {
        $questionnaireData = session('fatigue_questionnaire');
        
        if (!$questionnaireData) {
            return redirect()->route('fatigue.questionnaire')->with('error', 'Silakan isi kuesioner terlebih dahulu.');
        }

        return Inertia::render('Fatigue/ReactionTest', [
            'questionnaireStatus' => $questionnaireData['status']
        ]);
    }

    /**
     * Store the fatigue check result.
     */
    public function store(Request $request)
    {
        $request->validate([
            'questionnaire_status' => 'required|boolean',
            'reaction_time_ms' => 'required|integer',
        ]);

        $questionnaireData = session('fatigue_questionnaire');
        
        // Calculate dynamic is_fit: Questionnaire must be Fit AND Reaction Time must be <= 500ms
        $isFit = $request->questionnaire_status && ($request->reaction_time_ms <= 500);

        $fatigueCheck = FatigueCheck::create([
            'user_id' => $request->user()->id,
            'questionnaire_status' => $request->questionnaire_status,
            'reaction_time_ms' => $request->reaction_time_ms,
            'is_fit' => $isFit,
        ]);

        // Save individual questionnaire answers if available in session
        if ($questionnaireData && !empty($questionnaireData['answers'])) {
            foreach ($questionnaireData['answers'] as $qId => $ansVal) {
                // Check if the question exists
                if (FatigueQuestion::where('id', $qId)->exists()) {
                    FatigueCheckAnswer::create([
                        'fatigue_check_id' => $fatigueCheck->id,
                        'fatigue_question_id' => $qId,
                        'answer' => $ansVal,
                    ]);
                }
            }
        }

        // Update the user's status_fit based on the test result
        $request->user()->update([
            'status_fit' => $isFit
        ]);

        // Load the saved answers for display on the result page
        $loadedAnswers = FatigueCheckAnswer::with('question')
            ->where('fatigue_check_id', $fatigueCheck->id)
            ->get();

        // Store result in session for the result page
        session()->flash('fatigue_result', [
            'is_fit' => $fatigueCheck->is_fit,
            'reaction_time_ms' => $fatigueCheck->reaction_time_ms,
            'questionnaire_status' => $fatigueCheck->questionnaire_status,
            'answers' => $loadedAnswers
        ]);

        // Clear the questionnaire from session
        session()->forget('fatigue_questionnaire');

        return redirect()->route('fatigue.result');
    }

    /**
     * Display the final result page.
     */
    public function result()
    {
        $result = session('fatigue_result');

        if (!$result) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Fatigue/Result', [
            'result' => $result
        ]);
    }

    /**
     * Display the user's fatigue check history.
     */
    public function history(Request $request)
    {
        $fatigueChecks = FatigueCheck::with(['answers.question'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Fatigue/History', [
            'fatigueChecks' => $fatigueChecks
        ]);
    }
}
