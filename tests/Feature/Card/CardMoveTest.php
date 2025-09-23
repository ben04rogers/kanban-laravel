<?php

namespace Tests\Feature\Card;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use App\Models\BoardShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardMoveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and board for testing
        $this->user = User::factory()->create();
        $this->board = Board::factory()->create(['user_id' => $this->user->id]);
        
        // Create default columns for the board
        $this->todoColumn = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'To Do',
            'position' => 0
        ]);
        
        $this->inProgressColumn = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'In Progress',
            'position' => 1
        ]);
        
        $this->doneColumn = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Done',
            'position' => 2
        ]);
    }

    public function test_authenticated_user_can_move_card_within_same_column()
    {
        // Create cards in a column
        $card1 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 0,
        ]);
        
        $card2 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 1,
        ]);
        
        $card3 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 2,
        ]);

        // Move card3 to position 0 (should reorder all cards)
        $response = $this->actingAs($this->user)
            ->post("/cards/{$card3->id}/move", [
                'board_column_id' => $this->todoColumn->id,
                'position' => 0,
            ]);

        $response->assertRedirect();
        
        // Check that positions were updated correctly
        $this->assertDatabaseHas('cards', [
            'id' => $card3->id,
            'position' => 0,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $card1->id,
            'position' => 1,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $card2->id,
            'position' => 2,
        ]);
    }

    public function test_authenticated_user_can_move_card_to_different_column()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => 0,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'board_column_id' => $this->inProgressColumn->id,
            'position' => 0,
        ]);
    }

    public function test_card_move_reorders_cards_correctly_when_moving_to_different_column()
    {
        // Create cards in the destination column
        $existingCard1 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->inProgressColumn->id,
            'position' => 0,
        ]);
        
        $existingCard2 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->inProgressColumn->id,
            'position' => 1,
        ]);

        // Card to move
        $cardToMove = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 0,
        ]);

        // Move card to position 1 in the new column
        $response = $this->actingAs($this->user)
            ->post("/cards/{$cardToMove->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => 1,
            ]);

        $response->assertRedirect();
        
        // Check that positions were updated correctly
        $this->assertDatabaseHas('cards', [
            'id' => $existingCard1->id,
            'position' => 0,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $cardToMove->id,
            'board_column_id' => $this->inProgressColumn->id,
            'position' => 1,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $existingCard2->id,
            'position' => 2,
        ]);
    }

    public function test_card_move_validates_required_fields()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", []);

        $response->assertSessionHasErrors(['board_column_id', 'position']);
    }

    public function test_card_move_validates_column_exists()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => 99999, // Non-existent column
                'position' => 0,
            ]);

        $response->assertSessionHasErrors(['board_column_id']);
    }

    public function test_card_move_validates_position_is_integer()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => 'invalid', // Non-integer position
            ]);

        $response->assertSessionHasErrors(['position']);
    }

    public function test_card_move_validates_position_minimum()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => -1, // Negative position
            ]);

        $response->assertSessionHasErrors(['position']);
    }

    public function test_card_move_requires_authentication()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->post("/cards/{$card->id}/move", [
            'board_column_id' => $this->inProgressColumn->id,
            'position' => 0,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_card_move_to_end_of_column()
    {
        // Create cards in a column
        $card1 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 0,
        ]);
        
        $card2 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 1,
        ]);
        
        $card3 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 2,
        ]);

        // Move card1 to the end (position 2)
        $response = $this->actingAs($this->user)
            ->post("/cards/{$card1->id}/move", [
                'board_column_id' => $this->todoColumn->id,
                'position' => 2,
            ]);

        $response->assertRedirect();
        
        // Check that positions were updated correctly
        $this->assertDatabaseHas('cards', [
            'id' => $card2->id,
            'position' => 0,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $card3->id,
            'position' => 1,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $card1->id,
            'position' => 2,
        ]);
    }

    public function test_user_cannot_move_card_they_dont_have_access_to()
    {
        $otherUser = User::factory()->create();
        $otherBoard = Board::factory()->create(['user_id' => $otherUser->id]);
        $otherColumn = BoardColumn::factory()->create(['board_id' => $otherBoard->id]);
        
        $card = Card::factory()->create([
            'board_id' => $otherBoard->id,
            'board_column_id' => $otherColumn->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $otherColumn->id,
                'position' => 1,
            ]);

        $response->assertForbidden();
    }

    public function test_shared_user_can_move_card_on_shared_board()
    {
        $sharedUser = User::factory()->create();
        BoardShare::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $sharedUser->id,
        ]);

        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($sharedUser)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => 0,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'board_column_id' => $this->inProgressColumn->id,
        ]);
    }

    public function test_board_owner_can_move_card_on_their_board()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => 0,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'board_column_id' => $this->inProgressColumn->id,
        ]);
    }

    public function test_card_owner_can_move_their_own_card()
    {
        $cardOwner = User::factory()->create();
        
        // Share board with card owner
        BoardShare::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $cardOwner->id,
        ]);
        
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'user_id' => $cardOwner->id,
        ]);

        $response = $this->actingAs($cardOwner)
            ->post("/cards/{$card->id}/move", [
                'board_column_id' => $this->inProgressColumn->id,
                'position' => 0,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'board_column_id' => $this->inProgressColumn->id,
        ]);
    }
}
