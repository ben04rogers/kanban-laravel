<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use App\Models\BoardShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardDeleteTest extends TestCase
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

    public function test_authenticated_user_can_delete_card()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/cards/{$card->id}");

        $response->assertRedirect("/boards/{$this->board->id}");
        
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }

    public function test_card_deletion_requires_authentication()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->delete("/cards/{$card->id}");

        $response->assertRedirect('/login');
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
        ]);
    }

    public function test_card_deletion_redirects_to_correct_board()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/cards/{$card->id}");

        $response->assertRedirect("/boards/{$this->board->id}");
    }

    public function test_deleting_nonexistent_card_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->delete("/cards/99999");

        $response->assertStatus(404);
    }

    public function test_card_deletion_preserves_other_cards()
    {
        // Create multiple cards
        $card1 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card 1',
        ]);
        
        $card2 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card 2',
        ]);
        
        $card3 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card 3',
        ]);

        // Delete card2
        $response = $this->actingAs($this->user)
            ->delete("/cards/{$card2->id}");

        $response->assertRedirect();
        
        // Check that card2 is deleted but others remain
        $this->assertDatabaseMissing('cards', [
            'id' => $card2->id,
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $card1->id,
            'title' => 'Card 1',
        ]);
        
        $this->assertDatabaseHas('cards', [
            'id' => $card3->id,
            'title' => 'Card 3',
        ]);
    }

    public function test_card_deletion_with_assigned_user()
    {
        $assignedUser = User::factory()->create();
        
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'user_id' => $assignedUser->id,
            'title' => 'Assigned Card',
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/cards/{$card->id}");

        $response->assertRedirect("/boards/{$this->board->id}");
        
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }

    public function test_card_deletion_with_description()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card with Description',
            'description' => 'This card has a description',
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/cards/{$card->id}");

        $response->assertRedirect("/boards/{$this->board->id}");
        
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_user_cannot_delete_card_they_dont_have_access_to()
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
            ->delete("/cards/{$card->id}");

        $response->assertForbidden();
        
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
        ]);
    }

    public function test_shared_user_can_delete_card_on_shared_board()
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
            ->delete("/cards/{$card->id}");

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }

    public function test_board_owner_can_delete_card_on_their_board()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/cards/{$card->id}");

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }

    public function test_card_owner_can_delete_their_own_card()
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
            ->delete("/cards/{$card->id}");

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }
}
