<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Requests\ReorderColumnsRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = auth()->user();
        $withColumns = ['columns' => fn($query) => $query->orderBy('position')];
        
        $boards = collect()
            ->merge($user->boards()->with($withColumns)->get()->map(fn($board) => $board->setAttribute('is_owner', true)))
            ->merge($user->sharedBoards()->with($withColumns)->get()->map(fn($board) => $board->setAttribute('is_owner', false)));

        return Inertia::render('Boards/Index', compact('boards'));
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

        return Inertia::render('Boards/Show', [
            'board' => $board,
            'boardUsers' => $boardUsers
        ]);
    }

    public function store(StoreBoardRequest $request)
    {

        $board = auth()->user()->boards()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Create default columns
        $this->createDefaultColumns($board);

        return redirect()->route('boards.show', $board)
            ->with('success', 'Board created successfully!');
    }

    public function update(UpdateBoardRequest $request, Board $board)
    {

        $board->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()
            ->with('success', 'Board updated successfully!');
    }

    public function destroy(Board $board)
    {
        $this->authorize('delete', $board);

        $board->delete();

        return redirect()->route('boards.index')
            ->with('success', 'Board deleted successfully!');
    }

    public function reorderColumns(ReorderColumnsRequest $request, Board $board)
    {

        foreach ($request->columns as $columnData) {
            $board->columns()->where('id', $columnData['id'])->update([
                'position' => $columnData['position']
            ]);
        }

        // Return back to the board page with updated data
        return redirect()->back();
    }

    private function createDefaultColumns(Board $board)
    {
        $defaultColumns = [
            ['name' => 'To Do', 'position' => 0],
            ['name' => 'In Progress', 'position' => 1],
            ['name' => 'Testing', 'position' => 2],
            ['name' => 'Done', 'position' => 3],
        ];

        foreach ($defaultColumns as $column) {
            BoardColumn::create([
                'name' => $column['name'],
                'position' => $column['position'],
                'board_id' => $board->id,
            ]);
        }
    }
}
