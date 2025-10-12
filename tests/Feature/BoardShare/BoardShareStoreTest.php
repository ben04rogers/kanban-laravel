<?php

namespace Tests\Feature\BoardShare;

use App\Models\Board;
use App\Models\BoardShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardShareStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_share_board()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $board = Board::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->post("/boards/{$board->id}/shares", [
                'user_id' => $otherUser->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('board_shares', [
            'board_id' => $board->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_owner_cannot_share_board_with_themself()
    {
        $owner = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)
            ->post("/boards/{$board->id}/shares", [
                'user_id' => $owner->id,
            ]);

        $response->assertSessionHasErrors([
            'user_id' => 'Cannot share board with the owner',
        ]);

        $this->assertDatabaseMissing('board_shares', [
            'board_id' => $board->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_owner_cannot_share_board_with_same_user_twice()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $board = Board::factory()->create(['user_id' => $owner->id]);

        BoardShare::create([
            'board_id' => $board->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($owner)
            ->post("/boards/{$board->id}/shares", [
                'user_id' => $otherUser->id,
            ]);

        $response->assertSessionHasErrors([
            'user_id' => 'Board is already shared with this user',
        ]);

        $this->assertEquals(1, BoardShare::where('board_id', $board->id)->count());
    }

    public function test_guest_cannot_share_board()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $owner->id]);

        $response = $this->post("/boards/{$board->id}/shares", [
            'user_id' => $otherUser->id,
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('board_shares', [
            'board_id' => $board->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_sharing_board_requires_valid_user_id()
    {
        $owner = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)
            ->post("/boards/{$board->id}/shares", [
                'user_id' => 9999, // non-existent
            ]);

        $response->assertSessionHasErrors([
            'user_id' => 'The selected user id is invalid.',
        ]);
    }

    public function test_non_owner_cannot_share_board()
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $otherUser = User::factory()->create();

        $board = Board::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($intruder)
            ->post("/boards/{$board->id}/shares", [
                'user_id' => $otherUser->id,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('board_shares', [
            'board_id' => $board->id,
            'user_id' => $otherUser->id,
        ]);
    }
}
