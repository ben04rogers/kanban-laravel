<?php

namespace Tests\Feature\Board;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BoardShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_show_page_displays_correct_inertia_data()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Board',
            'description' => 'Test Description'
        ]);
        
        // Create some columns for the board
        BoardColumn::factory()->create(['board_id' => $board->id, 'name' => 'To Do', 'position' => 0]);
        BoardColumn::factory()->create(['board_id' => $board->id, 'name' => 'In Progress', 'position' => 1]);

        $response = $this->actingAs($user)
            ->get("/boards/{$board->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board', fn (Assert $boardAssert) => $boardAssert
                ->where('id', $board->id)
                ->where('name', 'Test Board')
                ->where('description', 'Test Description')
                ->has('columns', 2)
                ->has('columns.0', fn (Assert $column) => $column
                    ->where('name', 'To Do')
                    ->where('position', 0)
                    ->etc()
                )
                ->has('columns.1', fn (Assert $column) => $column
                    ->where('name', 'In Progress')
                    ->where('position', 1)
                    ->etc()
                )
                ->has('user')
                ->etc()
            )
            ->has('boardUsers')
        );
    }

    public function test_board_show_requires_authentication()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Board',
            'description' => 'Test Description'
        ]);

        $response = $this->get("/boards/{$board->id}");

        $response->assertRedirect('/login');
    }

    public function test_board_show_loads_board_with_all_columns()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Multi-Column Board',
            'description' => 'Board with many columns'
        ]);
        
        // Create multiple columns
        BoardColumn::factory()->create(['board_id' => $board->id, 'name' => 'To Do', 'position' => 0]);
        BoardColumn::factory()->create(['board_id' => $board->id, 'name' => 'In Progress', 'position' => 1]);
        BoardColumn::factory()->create(['board_id' => $board->id, 'name' => 'Testing', 'position' => 2]);
        BoardColumn::factory()->create(['board_id' => $board->id, 'name' => 'Done', 'position' => 3]);

        $response = $this->actingAs($user)
            ->get("/boards/{$board->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board.columns', 4)
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
                ->where('name', 'Testing')
                ->where('position', 2)
                ->etc()
            )
            ->has('board.columns.3', fn (Assert $column) => $column
                ->where('name', 'Done')
                ->where('position', 3)
                ->etc()
            )
        );
    }

    public function test_board_show_loads_board_with_user_information()
    {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'User Board',
            'description' => 'Board with user info'
        ]);

        $response = $this->actingAs($user)
            ->get("/boards/{$board->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('board.user', fn (Assert $userAssert) => $userAssert
                ->where('id', $user->id)
                ->where('name', 'John Doe')
                ->where('email', 'john@example.com')
                ->etc()
            )
        );
    }

    public function test_board_show_loads_board_users()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Shared Board',
            'description' => 'Board with shared users'
        ]);

        $response = $this->actingAs($user)
            ->get("/boards/{$board->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Show')
            ->has('boardUsers', 1) // Just the owner initially
            ->has('boardUsers.0', fn (Assert $userAssert) => $userAssert
                ->where('id', $user->id)
                ->etc()
            )
        );
    }

    public function test_board_show_with_nonexistent_board_returns_404()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get("/boards/99999");

        $response->assertStatus(404);
    }
}
