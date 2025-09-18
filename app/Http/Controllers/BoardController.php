<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class BoardController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $boards = auth()->user()->boards()->with(['columns' => function($query) {
            $query->orderBy('position');
        }])->get();

        return Inertia::render('Boards/Index', [
            'boards' => $boards
        ]);
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
            'user'
        ]);

        return Inertia::render('Boards/Show', [
            'board' => $board
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $board = auth()->user()->boards()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Create default columns
        $this->createDefaultColumns($board);

        return redirect()->route('boards.show', $board)
            ->with('success', 'Board created successfully!');
    }

    public function update(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

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
