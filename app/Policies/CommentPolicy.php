<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comment $comment): bool
    {
        return $this->canAccessComment($user, $comment);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id || $this->canAccessComment($user, $comment);
    }

    private function canAccessComment(User $user, Comment $comment)
    {
        $card = $comment->card;
        if (! $card) {
            return false;
        }

        $board = $card->board;
        if (! $board) {
            return false;
        }

        return $user->id === $board->user_id ||
               $board->shares()->where('user_id', $user->id)->exists();
    }

    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }
}
