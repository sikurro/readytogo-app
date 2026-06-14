<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Petugas\QuizController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use Illuminate\Http\Request;

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    $now = now();
    $activeEventQuiz = \App\Models\Quiz::where('is_active', true)
        ->where('is_daily_quiz', false)
        ->where(function($query) use ($now) {
            $query->where(function($q) use ($now) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })->where(function($q) use ($now) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', $now);
            });
        })
        ->first();

    $latestFatigueCheckToday = $request->user()->fatigueChecks()
        ->whereDate('created_at', now()->toDateString())
        ->latest()
        ->first();

    $statusBugarHariIni = $latestFatigueCheckToday ? $latestFatigueCheckToday->is_fit : null;

    return Inertia::render('Petugas/Dashboard', [
        'activeEventQuiz' => $activeEventQuiz,
        'statusBugarHariIni' => $statusBugarHariIni
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', function (Request $request) {
    if (!$request->user()->isAdmin()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Quiz Routes
    Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
    Route::get('/quiz/{quiz}/play', [QuizController::class, 'play'])->name('quiz.play');
    Route::post('/quiz/{quiz}/store', [QuizController::class, 'storeAttempt'])->name('quiz.store');
    Route::get('/quiz/attempts/{attempt}/summary', [QuizController::class, 'summary'])->name('quiz.summary');
    Route::get('/quiz/{quiz}/demo-summary', [QuizController::class, 'demoSummary'])->name('quiz.demo-summary');
    Route::get('/quiz/leaderboard', [QuizController::class, 'leaderboard'])->name('quiz.leaderboard');
    Route::get('/quiz/history', [QuizController::class, 'history'])->name('quiz.history');

    // Fatigue Check Routes
    Route::get('/fatigue/questionnaire', [\App\Http\Controllers\FatigueCheckController::class, 'index'])->name('fatigue.questionnaire');
    Route::post('/fatigue/questionnaire', [\App\Http\Controllers\FatigueCheckController::class, 'processQuestionnaire'])->name('fatigue.questionnaire.process');
    Route::get('/fatigue/reaction-test', [\App\Http\Controllers\FatigueCheckController::class, 'test'])->name('fatigue.test');
    Route::post('/fatigue/store', [\App\Http\Controllers\FatigueCheckController::class, 'store'])->name('fatigue.store');
    Route::get('/fatigue/result', [\App\Http\Controllers\FatigueCheckController::class, 'result'])->name('fatigue.result');
    Route::get('/fatigue/history', [\App\Http\Controllers\FatigueCheckController::class, 'history'])->name('fatigue.history');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/quiz/history', [App\Http\Controllers\Admin\QuizController::class, 'history'])->name('quiz.history');
    Route::get('/quiz/history/export', [App\Http\Controllers\Admin\QuizController::class, 'exportHistory'])->name('quiz.history.export');

    Route::get('/leaderboard/daily', [App\Http\Controllers\Admin\LeaderboardController::class, 'dailyIndex'])->name('leaderboard.daily');
    Route::get('/leaderboard/daily/export', [App\Http\Controllers\Admin\LeaderboardController::class, 'exportDaily'])->name('leaderboard.daily.export');
    Route::get('/leaderboard/daily/pdf', [App\Http\Controllers\Admin\LeaderboardController::class, 'exportDailyPdf'])->name('leaderboard.daily.pdf');
    Route::get('/leaderboard/event', [App\Http\Controllers\Admin\LeaderboardController::class, 'eventIndex'])->name('leaderboard.event');
    Route::get('/leaderboard/event/export', [App\Http\Controllers\Admin\LeaderboardController::class, 'exportEvent'])->name('leaderboard.event.export');


    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('locations', App\Http\Controllers\Admin\LocationController::class);
    Route::resource('quizzes', App\Http\Controllers\Admin\QuizController::class);
    Route::post('quizzes/{quiz}/attach-question', [App\Http\Controllers\Admin\QuizController::class, 'attachQuestion'])->name('quizzes.attach_question');
    Route::post('quizzes/{quiz}/detach-question', [App\Http\Controllers\Admin\QuizController::class, 'detachQuestion'])->name('quizzes.detach_question');
    
    Route::get('questions/template', [App\Http\Controllers\Admin\QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::get('questions/export', [App\Http\Controllers\Admin\QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions/import', [App\Http\Controllers\Admin\QuestionController::class, 'import'])->name('questions.import');
    Route::resource('questions', App\Http\Controllers\Admin\QuestionController::class);

    Route::resource('fatigue-questions', App\Http\Controllers\Admin\FatigueQuestionController::class);
    Route::get('fatigue-checks', [App\Http\Controllers\Admin\FatigueCheckController::class, 'index'])->name('fatigue-checks.index');

    Route::get('users/template', [App\Http\Controllers\Admin\UserController::class, 'downloadTemplate'])->name('users.template');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::post('users/import', [App\Http\Controllers\Admin\UserController::class, 'import'])->name('users.import');
    Route::post('users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
});

require __DIR__.'/auth.php';
