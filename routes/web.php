<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        $boards = auth()->user()->boards()->with(['columns' => function($query) {
            $query->orderBy('position');
        }])->get();
        return Inertia::render('Boards/Index', ['boards' => $boards]);
    }
    
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Board routes
    Route::resource('boards', BoardController::class);
    
    // Card routes
    Route::resource('cards', CardController::class)->except(['index', 'show']);
    Route::post('/cards/{card}/move', [CardController::class, 'move'])->name('cards.move');
});

require __DIR__.'/auth.php';
