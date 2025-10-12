<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $status = request()->get('status', 'active'); // Default to active boards
            $withColumns = ['columns' => fn ($query) => $query->orderBy('position')];

            // Get all boards that the user is the owner of or has access to, filtered by status
            $boards = collect()
                ->merge($user->boards()->where('status', $status)->with($withColumns)->get()->map(fn ($board) => $board->setAttribute('is_owner', true)))
                ->merge($user->sharedBoards()->where('status', $status)->with($withColumns)->get()->map(fn ($board) => $board->setAttribute('is_owner', false)));

            return Inertia::render('Boards/Index', compact('boards', 'status'));
        }

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
