<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Card;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreCommentRequest $request, Card $card)
    {
        $this->authorize('view', $card);

        $comment = Comment::create([
            'content' => $request->content,
            'card_id' => $card->id,
            'user_id' => auth()->id(),
        ]);

        $comment->load('user');

        return redirect()->route('cards.show', $card)
            ->with('success', 'Comment added successfully!');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $card = $comment->card;
        $comment->delete();

        return redirect()->route('cards.show', $card)
            ->with('success', 'Comment deleted successfully!');
    }
}
