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
    return Inertia::render('Petugas/Dashboard');
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
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/quiz/history', [App\Http\Controllers\Admin\QuizController::class, 'history'])->name('quiz.history');
    Route::get('/quiz/history/export', [App\Http\Controllers\Admin\QuizController::class, 'exportHistory'])->name('quiz.history.export');


    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('quizzes', App\Http\Controllers\Admin\QuizController::class);
    Route::post('quizzes/{quiz}/attach-question', [App\Http\Controllers\Admin\QuizController::class, 'attachQuestion'])->name('quizzes.attach_question');
    Route::post('quizzes/{quiz}/detach-question', [App\Http\Controllers\Admin\QuizController::class, 'detachQuestion'])->name('quizzes.detach_question');
    
    Route::get('questions/template', [App\Http\Controllers\Admin\QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::get('questions/export', [App\Http\Controllers\Admin\QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions/import', [App\Http\Controllers\Admin\QuestionController::class, 'import'])->name('questions.import');
    Route::resource('questions', App\Http\Controllers\Admin\QuestionController::class);

    Route::get('users/template', [App\Http\Controllers\Admin\UserController::class, 'downloadTemplate'])->name('users.template');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::post('users/import', [App\Http\Controllers\Admin\UserController::class, 'import'])->name('users.import');
    Route::post('users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
});

require __DIR__.'/auth.php';
