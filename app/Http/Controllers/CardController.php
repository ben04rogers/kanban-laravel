<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Http\Requests\MoveCardRequest;
use App\Services\CardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class CardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CardService $cardService) 
    {
    }

    public function show(Card $card)
    {
        $this->authorize('view', $card);

        $card->load(['board', 'column', 'user']);
        
        // Load the board with all necessary relationships
        $board = $card->board;
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
            'boardUsers' => $boardUsers,
            'cardId' => $card->id
        ]);
    }

    public function store(StoreCardRequest $request)
    {
        $board = Board::findOrFail($request->board_id);

        $this->cardService->createCard(
            $request->board_id,
            $request->board_column_id,
            $request->title,
            $request->description,
            $request->assigned_user_id
        );

        return redirect()->back()
            ->with('success', 'Card created successfully!');
    }

    public function update(UpdateCardRequest $request, Card $card)
    {

        $card->update([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $request->assigned_user_id,
        ]);

        return redirect()->back()
            ->with('success', 'Card updated successfully!');
    }

    public function move(MoveCardRequest $request, Card $card)
    {

        $oldColumnId = $card->board_column_id;
        $newColumnId = $request->board_column_id;
        $newPosition = $request->position;

        // If moving to a different column, update the column
        if ($oldColumnId !== $newColumnId) {
            $card->update(['board_column_id' => $newColumnId]);
        }

        // Reorder cards in the new column
        $this->reorderCardsInColumn($newColumnId, $card->id, $newPosition);

        return redirect()->back();
    }

    public function destroy(Card $card)
    {
        $this->authorize('delete', $card);

        $boardId = $card->board_id;
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
