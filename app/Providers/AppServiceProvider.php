<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Configure route model binding for UUIDs
        Route::model('board', Board::class);
        Route::model('card', Card::class);
    }
}
