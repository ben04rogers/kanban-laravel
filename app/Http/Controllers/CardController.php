<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveCardRequest;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Board;
use App\Models\Card;
use App\Services\CardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class CardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CardService $cardService) {}

    public function show(Card $card)
    {
        $this->authorize('view', $card);

        $card->load(['board', 'column', 'user', 'comments.user']);

        // Load the board with all necessary relationships
        $board = $card->board;
        $board->load([
            'columns' => function ($query) {
                $query->orderBy('position');
            },
            'columns.cards.user' => function ($query) {
                $query->orderBy('position');
            },
            'columns.cards.comments.user' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'user',
            'sharedWith',
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
            'cardId' => $card->id,
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
        $this->cardService->updateCard(
            $card,
            $request->title,
            $request->description,
            $request->assigned_user_id
        );

        return redirect()->back()
            ->with('success', 'Card updated successfully!');
    }

    public function move(MoveCardRequest $request, Card $card)
    {
        $this->cardService->moveCard(
            $card,
            $request->board_column_id,
            $request->position
        );

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
}
