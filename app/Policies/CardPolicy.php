<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    public function view(User $user, Card $card)
    {
        return $this->canAccessCard($user, $card);
    }

    public function update(User $user, Card $card)
    {
        return $this->canAccessCard($user, $card);
    }

    public function delete(User $user, Card $card)
    {
        return $this->canAccessCard($user, $card);
    }

    private function canAccessCard(User $user, Card $card)
    {
        // If user owns the card, allow access
        if ($user->id === $card->user_id) {
            return true;
        }

        $board = \App\Models\Board::find($card->board_id);
        if (! $board) {
            return false;
        }

        return $user->id === $board->user_id ||
               $board->shares()->where('user_id', $user->id)->exists();
    }
}
