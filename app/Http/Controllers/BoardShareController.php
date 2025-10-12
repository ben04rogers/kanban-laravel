<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardShare;
use App\Services\BoardShareService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class BoardShareController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private BoardShareService $boardShareService
    ) {}

    public function index(Board $board)
    {
        $this->authorize('view', $board);

        $shares = $this->boardShareService->getBoardShares($board);

        return response()->json([
            'shares' => $shares,
        ]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $users = $this->boardShareService->searchUsers($query);

        return response()->json(['users' => $users]);
    }

    public function store(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $this->boardShareService->shareBoard($board, $request->user_id);

            return back()->with('success', 'Board shared successfully');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['user_id' => $e->getMessage()]);
        }
    }

    public function destroy(Board $board, BoardShare $share)
    {
        $this->authorize('update', $board);

        $this->boardShareService->removeShare($share);

        return back()->with('success', 'Share removed successfully');
    }
}
