<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use App\Models\BoardShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardUpdateTest extends TestCase
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
    }

    public function test_authenticated_user_can_update_card()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => 'Updated Card Title',
                'description' => 'Updated description',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated Card Title',
            'description' => 'Updated description',
        ]);
    }

    public function test_card_update_with_user_assignment()
    {
        $assignedUser = User::factory()->create();
        BoardShare::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $assignedUser->id,
        ]);

        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => 'Assigned Card',
                'assigned_user_id' => $assignedUser->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Assigned Card',
            'user_id' => $assignedUser->id,
        ]);
    }

    public function test_card_update_validates_required_title()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => '', // Empty title
                'description' => 'Valid description',
            ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_card_update_validates_title_max_length()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => str_repeat('a', 256), // Exceeds 255 character limit
                'description' => 'Valid description',
            ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_card_update_requires_authentication()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->put("/cards/{$card->id}", [
            'title' => 'Unauthorized Update',
            'description' => 'This should fail',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_card_update_allows_empty_description()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'description' => 'Original description',
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => 'Updated Title',
                'description' => '', // Empty description should be allowed
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated Title',
            'description' => null, // Laravel converts empty strings to null
        ]);
    }

    public function test_card_update_allows_null_description()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'description' => 'Original description',
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => 'Updated Title',
                // description not provided - should default to null
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated Title',
            'description' => null,
        ]);
    }

    public function test_user_cannot_update_card_they_dont_have_access_to()
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
            ->put("/cards/{$card->id}", [
                'title' => 'Unauthorized Update',
            ]);

        $response->assertForbidden();
    }

    public function test_shared_user_can_update_card_on_shared_board()
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
            ->put("/cards/{$card->id}", [
                'title' => 'Updated by Shared User',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated by Shared User',
        ]);
    }

    public function test_board_owner_can_update_card_on_their_board()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put("/cards/{$card->id}", [
                'title' => 'Updated by Owner',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated by Owner',
        ]);
    }

    public function test_card_owner_can_update_their_own_card()
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
            ->put("/cards/{$card->id}", [
                'title' => 'Updated by Card Owner',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated by Card Owner',
        ]);
    }
}
