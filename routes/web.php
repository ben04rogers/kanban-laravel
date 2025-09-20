<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardShareController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index']);

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
    Route::resource('cards', CardController::class)->except(['index']);
    Route::post('/cards/{card}/move', [CardController::class, 'move'])->name('cards.move');
    
    // Board sharing routes
    Route::get('/boards/{board}/shares', [BoardShareController::class, 'index'])->name('boards.shares');
    Route::get('/users/search', [BoardShareController::class, 'searchUsers'])->name('users.search');
    Route::post('/boards/{board}/shares', [BoardShareController::class, 'store'])->name('boards.shares.store');
    Route::delete('/boards/{board}/shares/{share}', [BoardShareController::class, 'destroy'])->name('boards.shares.destroy');
});

// Broadcasting authentication routes
Route::middleware(['auth', 'web'])->group(function () {
    Route::post('/broadcasting/auth', function () {
        return response()->json(['status' => 'success']);
    });
    
    // Test route for debugging
    Route::get('/test-broadcast/{boardId}', function ($boardId) {
        $card = \App\Models\Card::where('board_id', $boardId)->first();
        if ($card) {
            broadcast(new \App\Events\CardMoved($card, 1, 2, 0));
            return response()->json(['message' => 'Test broadcast sent']);
        }
        return response()->json(['error' => 'No card found']);
    });
});

require __DIR__.'/auth.php';
