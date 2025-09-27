<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardShareController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Board routes
    Route::resource('boards', BoardController::class);
    Route::post('/boards/{board}/columns/reorder', [BoardController::class, 'reorderColumns'])->name('boards.columns.reorder');

    // Card routes
    Route::resource('cards', CardController::class)->except(['index']);
    Route::post('/cards/{card}/move', [CardController::class, 'move'])->name('cards.move');

    // Board sharing routes
    Route::get('/boards/{board}/shares', [BoardShareController::class, 'index'])->name('boards.shares');
    Route::get('/users/search', [BoardShareController::class, 'searchUsers'])->name('users.search');
    Route::post('/boards/{board}/shares', [BoardShareController::class, 'store'])->name('boards.shares.store');
    Route::delete('/boards/{board}/shares/{share}', [BoardShareController::class, 'destroy'])->name('boards.shares.destroy');
});

require __DIR__.'/auth.php';
