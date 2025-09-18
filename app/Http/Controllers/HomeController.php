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
            // Get owned boards
            $ownedBoards = auth()->user()->boards()->with(['columns' => function($query) {
                $query->orderBy('position');
            }])->get();
            
            // Get shared boards
            $sharedBoards = auth()->user()->sharedBoards()->with(['columns' => function($query) {
                $query->orderBy('position');
            }])->get();
            
            // Combine and mark ownership
            $allBoards = $ownedBoards->map(function($board) {
                $board->is_owner = true;
                return $board;
            })->concat(
                $sharedBoards->map(function($board) {
                    $board->is_owner = false;
                    return $board;
                })
            );
            
            return Inertia::render('Boards/Index', ['boards' => $allBoards]);
        }
        
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }
}