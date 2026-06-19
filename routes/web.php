<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Petugas\QuizController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FatigueCheckController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\FatigueQuestionController;
use App\Http\Controllers\Admin\FatigueCheckController as AdminFatigueCheckController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LeaderboardController;
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

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/admin/dashboard', [DashboardController::class, 'adminIndex'])->middleware(['auth', 'verified'])->name('admin.dashboard');
Route::get('/admin/dashboard/chart-data', [DashboardController::class, 'chartData'])->middleware(['auth', 'verified'])->name('admin.dashboard.chart-data');
Route::get('/admin/dashboard/fatigue-details', [DashboardController::class, 'fatigueDetails'])->middleware(['auth', 'verified'])->name('admin.dashboard.fatigue-details');
Route::get('/admin/dashboard/incident-details', [DashboardController::class, 'incidentDetails'])->middleware(['auth', 'verified'])->name('admin.dashboard.incident-details');

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
    Route::get('/fatigue/hub', [FatigueCheckController::class, 'hub'])->name('fatigue.hub');
    Route::get('/fatigue/questionnaire', [FatigueCheckController::class, 'index'])->name('fatigue.questionnaire');
    Route::post('/fatigue/questionnaire', [FatigueCheckController::class, 'processQuestionnaire'])->name('fatigue.questionnaire.process');
    Route::get('/fatigue/reaction-test', [FatigueCheckController::class, 'test'])->name('fatigue.test');
    Route::post('/fatigue/store', [FatigueCheckController::class, 'store'])->name('fatigue.store');
    Route::get('/fatigue/result', [FatigueCheckController::class, 'result'])->name('fatigue.result');
    Route::get('/fatigue/history', [FatigueCheckController::class, 'history'])->name('fatigue.history');

    // Incident Routes
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::get('/notifications/unread', [IncidentController::class, 'getUnreadNotifications'])->name('notifications.unread');
    Route::post('/notifications/mark-as-read', [IncidentController::class, 'markNotificationsAsRead'])->name('notifications.mark-as-read');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/quiz/history', [AdminQuizController::class, 'history'])->name('quiz.history');
    Route::get('/quiz/history/export', [AdminQuizController::class, 'exportHistory'])->name('quiz.history.export');

    Route::get('/leaderboard/daily', [LeaderboardController::class, 'dailyIndex'])->name('leaderboard.daily');
    Route::get('/leaderboard/daily/export', [LeaderboardController::class, 'exportDaily'])->name('leaderboard.daily.export');
    Route::get('/leaderboard/daily/pdf', [LeaderboardController::class, 'exportDailyPdf'])->name('leaderboard.daily.pdf');
    Route::get('/leaderboard/event', [LeaderboardController::class, 'eventIndex'])->name('leaderboard.event');
    Route::get('/leaderboard/event/export', [LeaderboardController::class, 'exportEvent'])->name('leaderboard.event.export');

    Route::resource('categories', CategoryController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('quizzes', AdminQuizController::class);
    Route::post('quizzes/{quiz}/attach-question', [AdminQuizController::class, 'attachQuestion'])->name('quizzes.attach_question');
    Route::post('quizzes/{quiz}/detach-question', [AdminQuizController::class, 'detachQuestion'])->name('quizzes.detach_question');
    
    Route::get('questions/template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::get('questions/export', [QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::resource('questions', QuestionController::class);

    Route::resource('fatigue-questions', FatigueQuestionController::class);
    Route::get('fatigue-checks/export', [AdminFatigueCheckController::class, 'export'])->name('fatigue-checks.export');
    Route::get('fatigue-checks', [AdminFatigueCheckController::class, 'index'])->name('fatigue-checks.index');

    Route::get('users/template', [UserController::class, 'downloadTemplate'])->name('users.template');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserController::class);

    // Admin Incident Management Routes
    Route::get('incidents/dashboard', [IncidentController::class, 'dashboard'])->name('incidents.dashboard');
    Route::get('incidents/export', [IncidentController::class, 'adminExport'])->name('incidents.export');
    Route::get('incidents', [IncidentController::class, 'adminIndex'])->name('incidents.index');
    Route::put('incidents/{incident}/status', [IncidentController::class, 'updateStatus'])->name('incidents.update-status');
});

require __DIR__.'/auth.php';
