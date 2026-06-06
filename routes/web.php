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
    Route::get('/quiz/play', [QuizController::class, 'play'])->name('quiz.play');
    Route::post('/quiz/store', [QuizController::class, 'storeAttempt'])->name('quiz.store');
    Route::get('/quiz/leaderboard', [QuizController::class, 'leaderboard'])->name('quiz.leaderboard');
    Route::get('/quiz/history', [QuizController::class, 'history'])->name('quiz.history');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/quiz/history', [App\Http\Controllers\Admin\QuizController::class, 'history'])->name('admin.quiz.history');
});

require __DIR__.'/auth.php';
