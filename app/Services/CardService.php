<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Card;

class CardService
{
    public function createCard(
        string $boardId,
        string $boardColumnId,
        string $title,
        ?string $description = null,
        ?string $assignedUserId = null
    ): Card {
        $board = Board::findOrFail($boardId);

        // Find the highest position in the column
        $maxPosition = Card::where('board_column_id', $boardColumnId)->max('position') ?? 0;

        return Card::create([
            'title'           => $title,
            'description'     => $description,
            'board_id'        => $board->id,
            'board_column_id' => $boardColumnId,
            'user_id'         => $assignedUserId,
            'position'        => $maxPosition + 1,
        ]);
    }

    public function updateCard(
        Card $card,
        string $title,
        ?string $description = null,
        ?string $assigned_user_id = null,
    ): Card {
        $card->update([
            'title' => $title,
            'description' => $description,
            'user_id' => $assigned_user_id,
        ]);

        return $card->fresh();
    }
}
