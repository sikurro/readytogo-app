<?php

namespace App\Http\Controllers;

use App\Models\FatigueCheck;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FatigueCheckController extends Controller
{
    /**
     * Display the initial questionnaire page.
     */
    public function index()
    {
        return Inertia::render('Fatigue/Questionnaire');
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
        // Require the questionnaire data to be passed via session or query
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
            'is_fit' => 'required|boolean',
        ]);

        $fatigueCheck = FatigueCheck::create([
            'user_id' => $request->user()->id,
            'questionnaire_status' => $request->questionnaire_status,
            'reaction_time_ms' => $request->reaction_time_ms,
            'is_fit' => $request->is_fit,
        ]);

        // Update the user's status_fit based on the test result
        $request->user()->update([
            'status_fit' => $request->is_fit
        ]);

        // Store result in session for the result page
        session()->flash('fatigue_result', [
            'is_fit' => $fatigueCheck->is_fit,
            'reaction_time_ms' => $fatigueCheck->reaction_time_ms,
            'questionnaire_status' => $fatigueCheck->questionnaire_status
        ]);

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
}
