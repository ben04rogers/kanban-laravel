<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BoardShareController extends Controller
{
    use AuthorizesRequests;

    public function index(Board $board)
    {
        $this->authorize('view', $board);

        $board->load('shares.user');
        
        return response()->json([
            'shares' => $board->shares
        ]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['users' => []]);
        }

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json(['users' => $users]);
    }

    public function store(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Check if user is not the board owner
        if ($board->user_id == $request->user_id) {
            return back()->withErrors(['user_id' => 'Cannot share board with the owner']);
        }

        // Check if already shared
        $existingShare = BoardShare::where('board_id', $board->id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingShare) {
            return back()->withErrors(['user_id' => 'Board is already shared with this user']);
        }

        $share = BoardShare::create([
            'board_id' => $board->id,
            'user_id' => $request->user_id
        ]);

        return back()->with('success', 'Board shared successfully');
    }

    public function destroy(Board $board, BoardShare $share)
    {
        $this->authorize('update', $board);

        $share->delete();

        return back()->with('success', 'Share removed successfully');
    }
}