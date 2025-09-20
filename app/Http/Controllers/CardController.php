<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Events\CardCreated;
use App\Events\CardUpdated;
use App\Events\CardMoved;
use App\Events\CardDeleted;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class CardController extends Controller
{
    use AuthorizesRequests;

    public function show(Card $card)
    {
        $this->authorize('view', $card);

        $card->load(['board', 'column', 'user']);
        
        // Load the board with all necessary relationships
        $board = $card->board;
        $board->load(['columns.cards.user' => function($query) {
            $query->orderBy('position');
        }]);

        return Inertia::render('Boards/Show', [
            'board' => $board,
            'cardId' => $card->id
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'board_id' => 'required|exists:boards,id',
            'board_column_id' => 'required|exists:board_columns,id',
        ]);

        $board = Board::findOrFail($request->board_id);
        $this->authorize('view', $board);

        // Get the highest position in the column
        $maxPosition = Card::where('board_column_id', $request->board_column_id)
            ->max('position') ?? 0;

        $card = Card::create([
            'title' => $request->title,
            'description' => $request->description,
            'board_id' => $request->board_id,
            'board_column_id' => $request->board_column_id,
            'user_id' => auth()->id(),
            'position' => $maxPosition + 1,
        ]);

        // Broadcast the card creation event
        broadcast(new CardCreated($card));

        return redirect()->back()
            ->with('success', 'Card created successfully!');
    }

    public function update(Request $request, Card $card)
    {
        $this->authorize('update', $card);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $card->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // Broadcast the card update event
        broadcast(new CardUpdated($card));

        return redirect()->back()
            ->with('success', 'Card updated successfully!');
    }

    public function move(Request $request, Card $card)
    {
        $this->authorize('update', $card);

        $request->validate([
            'board_column_id' => 'required|exists:board_columns,id',
            'position' => 'required|integer|min:0',
        ]);

        $oldColumnId = $card->board_column_id;
        $newColumnId = $request->board_column_id;
        $newPosition = $request->position;

        // If moving to a different column, update the column
        if ($oldColumnId !== $newColumnId) {
            $card->update(['board_column_id' => $newColumnId]);
        }

        // Reorder cards in the new column
        $this->reorderCardsInColumn($newColumnId, $card->id, $newPosition);

        // Refresh the card to get updated data
        $card->refresh();

        // Broadcast the card move event
        \Log::info('Broadcasting CardMoved event', [
            'card_id' => $card->id,
            'board_id' => $card->board_id,
            'old_column' => $oldColumnId,
            'new_column' => $newColumnId,
            'new_position' => $newPosition
        ]);
        broadcast(new CardMoved($card, $oldColumnId, $newColumnId, $newPosition));

        return redirect()->back();
    }

    public function destroy(Card $card)
    {
        $this->authorize('delete', $card);

        $boardId = $card->board_id;
        $columnId = $card->board_column_id;
        $cardId = $card->id;

        // Broadcast the card deletion event before deleting
        broadcast(new CardDeleted($cardId, $boardId, $columnId));

        $card->delete();

        return redirect()->route('boards.show', $boardId)
            ->with('success', 'Card deleted successfully!');
    }

    private function reorderCardsInColumn($columnId, $cardId, $newPosition)
    {
        // Get all cards in the column except the one being moved
        $cards = Card::where('board_column_id', $columnId)
            ->where('id', '!=', $cardId)
            ->orderBy('position')
            ->get();

        // Insert the moved card at the new position
        $cards->splice($newPosition, 0, [Card::find($cardId)]);

        // Update positions
        foreach ($cards as $index => $card) {
            $card->update(['position' => $index]);
        }
    }
}
