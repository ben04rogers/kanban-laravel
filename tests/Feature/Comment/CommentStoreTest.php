<?php

namespace Tests\Feature\Comment;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_comment_to_card()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
        $column = BoardColumn::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create([
            'board_id' => $board->id,
            'board_column_id' => $column->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->post(route('comments.store', $card), [
            'content' => 'This is a test comment'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'content' => 'This is a test comment',
            'card_id' => $card->id,
            'user_id' => $user->id
        ]);
    }

    public function test_comment_validation_empty_content()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
        $column = BoardColumn::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create([
            'board_id' => $board->id,
            'board_column_id' => $column->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->post(route('comments.store', $card), [
            'content' => ''
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_comment_validation_content_too_long()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
        $column = BoardColumn::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create([
            'board_id' => $board->id,
            'board_column_id' => $column->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->post(route('comments.store', $card), [
            'content' => str_repeat('a', 1001)
        ]);

        $response->assertSessionHasErrors(['content']);
    }
}
