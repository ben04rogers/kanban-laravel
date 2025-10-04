<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Requests\ReorderColumnsRequest;
use App\Services\BoardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private BoardService $boardService) 
    {
    }

    public function index()
    {
        $user = auth()->user();
        $status = request()->get('status', 'active'); // Default to active boards
        $withColumns = ['columns' => fn($query) => $query->orderBy('position')];
        
        $boards = collect()
            ->merge($user->boards()->where('status', $status)->with($withColumns)->get()->map(fn($board) => $board->setAttribute('is_owner', true)))
            ->merge($user->sharedBoards()->where('status', $status)->with($withColumns)->get()->map(fn($board) => $board->setAttribute('is_owner', false)));

        return Inertia::render('Boards/Index', compact('boards', 'status'));
    }

    public function show(Board $board)
    {
        $this->authorize('view', $board);

        $board->load([
            'columns' => function($query) {
                $query->orderBy('position');
            },
            'columns.cards.user' => function($query) {
                $query->orderBy('position');
            },
            'user',
            'sharedWith'
        ]);

        // Get all users with access to the board (owner + shared users)
        $boardUsers = collect()
            ->push($board->user)
            ->merge($board->sharedWith)
            ->unique('id')
            ->values();

        // Check if current user is the board creator
        $board->is_creator = auth()->id() === $board->user_id;

        return Inertia::render('Boards/Show', [
            'board' => $board,
            'boardUsers' => $boardUsers
        ]);
    }

    public function store(StoreBoardRequest $request)
    {
        $board = $this->boardService->createBoard(
            auth()->id(),
            $request->name,
            $request->description
        );

        return redirect()->route('boards.show', $board)
            ->with('success', 'Board created successfully!');
    }

    public function update(UpdateBoardRequest $request, Board $board)
    {
        $this->boardService->updateBoard(
            $board,
            $request->name,
            $request->description,
            $request->status
        );

        return redirect()->back()
            ->with('success', 'Board updated successfully!');
    }

    public function destroy(Board $board)
    {
        $this->authorize('delete', $board);

        $this->boardService->deleteBoard($board);

        return redirect()->route('boards.index')
            ->with('success', 'Board deleted successfully!');
    }

    public function reorderColumns(ReorderColumnsRequest $request, Board $board)
    {
        $this->boardService->reorderColumns($board, $request->columns);

        return redirect()->back();
    }

    public function updateStatus(Board $board)
    {
        $this->authorize('update', $board);
        
        $request = request();
        $status = $request->validate([
            'status' => 'required|in:active,completed,archived'
        ])['status'];

        $board->update(['status' => $status]);

        $statusMessages = [
            'active' => 'Board marked as active',
            'completed' => 'Board marked as completed',
            'archived' => 'Board archived'
        ];

        return redirect()->back()
            ->with('success', $statusMessages[$status] . ' successfully!');
    }

}
