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

        $currentMonth = date('Y-m');
        $history = QuizAttempt::with(['user', 'quiz'])
            ->where('month_year', $currentMonth)
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Quiz/History', [
            'history' => $history,
            'currentMonth' => $currentMonth,
        ]);
    }
}
