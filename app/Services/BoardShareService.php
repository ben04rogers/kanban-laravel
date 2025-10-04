<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BoardShareService
{
    public function getBoardShares(Board $board): Collection
    {
        return $board->shares()->with('user')->get();
    }

    public function searchUsers(string $query, int $limit = 10): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'name', 'email']);
    }

    public function shareBoard(Board $board, string $userId): BoardShare
    {
        // Check if user is not the board owner
        if ($board->user_id == $userId) {
            throw new \InvalidArgumentException('Cannot share board with the owner');
        }

        // Check if already shared
        $existingShare = BoardShare::where('board_id', $board->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingShare) {
            throw new \InvalidArgumentException('Board is already shared with this user');
        }

        return BoardShare::create([
            'board_id' => $board->id,
            'user_id' => $userId
        ]);
    }

    public function removeShare(BoardShare $share): bool
    {
        return $share->delete();
    }

    public function isBoardSharedWithUser(Board $board, string $userId): bool
    {
        return BoardShare::where('board_id', $board->id)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getSharedBoardsForUser(string $userId): Collection
    {
        return Board::whereHas('shares', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }
}
