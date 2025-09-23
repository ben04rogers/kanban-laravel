<?php

namespace Tests\Feature\Board;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BoardIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_index_page_displays_user_boards()
    {
        $user = User::factory()->create();
        $board1 = Board::factory()->create(['user_id' => $user->id, 'name' => 'My Board 1']);
        $board2 = Board::factory()->create(['user_id' => $user->id, 'name' => 'My Board 2']);

        $response = $this->actingAs($user)
            ->get('/boards');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Index')
            ->has('boards', 2)
            ->has('boards.0', fn (Assert $board) => $board
                ->where('name', 'My Board 1')
                ->where('is_owner', true)
                ->etc()
            )
            ->has('boards.1', fn (Assert $board) => $board
                ->where('name', 'My Board 2')
                ->where('is_owner', true)
                ->etc()
            )
        );
    }

    public function test_board_index_requires_authentication()
    {
        $response = $this->get('/boards');

        $response->assertRedirect('/login');
    }

    public function test_board_index_displays_empty_list_when_user_has_no_boards()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/boards');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Index')
            ->has('boards', 0)
        );
    }

    public function test_board_index_displays_boards_with_descriptions()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Board with Description',
            'description' => 'This is a test description'
        ]);

        $response = $this->actingAs($user)
            ->get('/boards');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Index')
            ->has('boards', 1)
            ->has('boards.0', fn (Assert $boardAssert) => $boardAssert
                ->where('name', 'Board with Description')
                ->where('description', 'This is a test description')
                ->where('is_owner', true)
                ->etc()
            )
        );
    }

    public function test_board_index_displays_boards_without_descriptions()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'user_id' => $user->id,
            'name' => 'Board without Description',
            'description' => null
        ]);

        $response = $this->actingAs($user)
            ->get('/boards');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Index')
            ->has('boards', 1)
            ->has('boards.0', fn (Assert $boardAssert) => $boardAssert
                ->where('name', 'Board without Description')
                ->where('description', null)
                ->where('is_owner', true)
                ->etc()
            )
        );
    }

    public function test_board_index_does_not_display_other_users_boards()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $user1Board = Board::factory()->create(['user_id' => $user1->id, 'name' => 'User 1 Board']);
        $user2Board = Board::factory()->create(['user_id' => $user2->id, 'name' => 'User 2 Board']);

        $response = $this->actingAs($user1)
            ->get('/boards');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Index')
            ->has('boards', 1)
            ->has('boards.0', fn (Assert $board) => $board
                ->where('name', 'User 1 Board')
                ->where('is_owner', true)
                ->etc()
            )
        );
    }

    public function test_board_index_displays_multiple_boards_in_correct_order()
    {
        $user = User::factory()->create();
        
        // Create boards in different order
        $board3 = Board::factory()->create(['user_id' => $user->id, 'name' => 'Third Board']);
        $board1 = Board::factory()->create(['user_id' => $user->id, 'name' => 'First Board']);
        $board2 = Board::factory()->create(['user_id' => $user->id, 'name' => 'Second Board']);

        $response = $this->actingAs($user)
            ->get('/boards');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Boards/Index')
            ->has('boards', 3)
            ->has('boards.0', fn (Assert $board) => $board
                ->where('name', 'Third Board')
                ->etc()
            )
            ->has('boards.1', fn (Assert $board) => $board
                ->where('name', 'First Board')
                ->etc()
            )
            ->has('boards.2', fn (Assert $board) => $board
                ->where('name', 'Second Board')
                ->etc()
            )
        );
    }
}
