<?php

namespace Tests\Feature\Card;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use App\Models\BoardShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CardShowTest extends TestCase
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

    public function test_authenticated_user_can_view_card_detail()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Test Card',
            'description' => 'Test Description',
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board', fn (Assert $boardAssert) => $boardAssert
                ->where('id', $this->board->id)
                ->where('name', $this->board->name)
                ->has('columns')
                ->has('user')
                ->etc()
            )
            ->has('boardUsers')
            ->where('cardId', $card->id)
        );
    }

    public function test_card_show_loads_card_with_assigned_user()
    {
        $assignedUser = User::factory()->create();
        
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Assigned Card',
            'description' => 'Card with assigned user',
            'user_id' => $assignedUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->where('cardId', $card->id)
        );
    }

    public function test_card_show_loads_board_with_all_columns()
    {
        // Create additional columns
        $inProgressColumn = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'In Progress',
            'position' => 1
        ]);
        
        $doneColumn = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Done',
            'position' => 2
        ]);

        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board.columns', 3)
            ->has('board.columns.0', fn (Assert $column) => $column
                ->where('name', 'To Do')
                ->where('position', 0)
                ->etc()
            )
            ->has('board.columns.1', fn (Assert $column) => $column
                ->where('name', 'In Progress')
                ->where('position', 1)
                ->etc()
            )
            ->has('board.columns.2', fn (Assert $column) => $column
                ->where('name', 'Done')
                ->where('position', 2)
                ->etc()
            )
            ->where('cardId', $card->id)
        );
    }

    public function test_card_show_loads_board_with_cards_in_columns()
    {
        // Create cards in different columns
        $card1 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card 1',
            'position' => 0,
        ]);
        
        $card2 = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card 2',
            'position' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card1->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board.columns.0.cards', 2)
            ->has('board.columns.0.cards.0', fn (Assert $card) => $card
                ->where('title', 'Card 1')
                ->where('position', 0)
                ->etc()
            )
            ->has('board.columns.0.cards.1', fn (Assert $card) => $card
                ->where('title', 'Card 2')
                ->where('position', 1)
                ->etc()
            )
            ->where('cardId', $card1->id)
        );
    }

    public function test_card_show_loads_board_users()
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

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('boardUsers', 2) // Owner + shared user
            ->where('cardId', $card->id)
        );
    }

    public function test_card_show_requires_authentication()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->get("/cards/{$card->id}");

        $response->assertRedirect('/login');
    }

    public function test_card_show_with_nonexistent_card_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->get("/cards/99999");

        $response->assertStatus(404);
    }

    public function test_card_show_passes_correct_card_id_to_frontend()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Specific Card',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->where('cardId', $card->id)
        );
    }

    public function test_card_show_loads_cards_with_user_information()
    {
        $assignedUser = User::factory()->create(['name' => 'John Doe']);
        
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
            'title' => 'Card with User',
            'user_id' => $assignedUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board.columns.0.cards.0.user', fn (Assert $user) => $user
                ->where('id', $assignedUser->id)
                ->where('name', 'John Doe')
                ->etc()
            )
            ->where('cardId', $card->id)
        );
    }

    public function test_user_cannot_view_card_they_dont_have_access_to()
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
            ->get("/cards/{$card->id}");

        $response->assertForbidden();
    }

    public function test_shared_user_can_view_card_on_shared_board()
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
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
    }

    public function test_board_owner_can_view_card_on_their_board()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
    }

    public function test_card_owner_can_view_their_own_card()
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
            ->get("/cards/{$card->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Boards/Show')
            ->where('cardId', $card->id)
        );
    }

    public function test_unauthenticated_user_cannot_view_cards()
    {
        $card = Card::factory()->create([
            'board_id' => $this->board->id,
            'board_column_id' => $this->todoColumn->id,
        ]);

        $response = $this->get("/cards/{$card->id}");

        $response->assertRedirect('/login');
    }
}
