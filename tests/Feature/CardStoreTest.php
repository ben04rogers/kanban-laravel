<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use App\Models\BoardShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardStoreTest extends TestCase
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
    }

    public function test_authenticated_user_can_create_card()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Test Card',
                'description' => 'This is a test card description',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'title' => 'Test Card',
            'description' => 'This is a test card description',
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'user_id' => null, // No user assigned
            'position' => 1, // First card in column
        ]);
    }

    public function test_card_creation_with_assigned_user()
    {
        $assignedUser = User::factory()->create();
        
        // Share board with the assigned user
        BoardShare::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $assignedUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Assigned Card',
                'description' => 'Card assigned to a user',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
                'assigned_user_id' => $assignedUser->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'title' => 'Assigned Card',
            'description' => 'Card assigned to a user',
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'user_id' => $assignedUser->id,
        ]);
    }

    public function test_card_creation_sets_correct_position()
    {
        // Create existing cards in the column
        Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 1,
        ]);
        
        Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'New Card',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'title' => 'New Card',
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'position' => 3, // Should be at position 3 (highest + 1)
        ]);
    }

    public function test_card_creation_without_description()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Card Without Description',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'title' => 'Card Without Description',
            'description' => null,
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);
    }

    public function test_card_creation_requires_authentication()
    {
        $response = $this->post('/cards', [
            'title' => 'Unauthorized Card',
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_card_creation_validates_required_fields()
    {
        // Test with valid board_id but missing other required fields
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'board_id' => $this->board->id,
            ]);

        $response->assertSessionHasErrors(['title']);
        $response->assertSessionHasErrors(['board_column_id']);
    }

    public function test_card_creation_validates_title_max_length()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => str_repeat('a', 256), // Exceeds 255 character limit
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_card_creation_validates_description_max_length()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Valid Title',
                'description' => str_repeat('a', 50001), // Exceeds 50000 character limit
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_card_creation_validates_board_exists()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Test Card',
                'board_id' => 99999, // Non-existent board
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertStatus(403); // Should be forbidden due to authorization check
    }

    public function test_card_creation_validates_column_exists()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Test Card',
                'board_id' => $this->board->id,
                'board_column_id' => 99999, // Non-existent column
            ]);

        $response->assertSessionHasErrors(['board_column_id']);
    }

    public function test_card_creation_prevents_assigning_user_without_board_access()
    {
        $userWithoutAccess = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Test Card',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
                'assigned_user_id' => $userWithoutAccess->id,
            ]);

        $response->assertSessionHasErrors(['assigned_user_id']);
    }

    public function test_user_cannot_create_card_on_board_they_dont_have_access_to()
    {
        $otherUser = User::factory()->create();
        $otherBoard = Board::factory()->create(['user_id' => $otherUser->id]);
        $otherColumn = BoardColumn::factory()->create(['board_id' => $otherBoard->id]);

        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Unauthorized Card',
                'board_id' => $otherBoard->id,
                'board_column_id' => $otherColumn->id,
            ]);

        $response->assertForbidden();
    }

    public function test_shared_user_can_create_card_on_shared_board()
    {
        $sharedUser = User::factory()->create();
        BoardShare::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $sharedUser->id,
        ]);

        $response = $this->actingAs($sharedUser)
            ->post('/cards', [
                'title' => 'Card by Shared User',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'title' => 'Card by Shared User',
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);
    }

    public function test_board_owner_can_create_card_on_their_board()
    {
        $response = $this->actingAs($this->user)
            ->post('/cards', [
                'title' => 'Card by Owner',
                'board_id' => $this->board->id,
                'board_column_id' => $this->todoColumn->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cards', [
            'title' => 'Card by Owner',
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);
    }
}
