<?php

namespace Tests\Feature\Comment;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_own_comment()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
        $column = BoardColumn::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create([
            'board_id' => $board->id,
            'board_column_id' => $column->id,
            'user_id' => $user->id,
        ]);
        $comment = Comment::factory()->create([
            'card_id' => $card->id,
            'user_id' => $user->id,
            'content' => 'Test comment',
        ]);

        $response = $this->actingAs($user)->delete(route('comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_other_users_comment()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user1->id]);
        $column = BoardColumn::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create([
            'board_id' => $board->id,
            'board_column_id' => $column->id,
            'user_id' => $user1->id,
        ]);
        $comment = Comment::factory()->create([
            'card_id' => $card->id,
            'user_id' => $user1->id,
            'content' => 'Test comment',
        ]);

        $response = $this->actingAs($user2)->delete(route('comments.destroy', $comment));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
