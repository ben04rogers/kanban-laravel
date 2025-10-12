<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function view(User $user, Board $board)
    {
        // User can view if they own the board or if it's shared with them
        return $user->id === $board->user_id ||
               $board->shares()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Board $board)
    {
        return $user->id === $board->user_id;
    }

    public function delete(User $user, Board $board)
    {
        return $user->id === $board->user_id;
    }
}
