<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_board()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/boards', [
                'name' => 'My Test Board',
                'description' => 'This is a test board description'
            ]);

        $response->assertRedirect();
        
        // Get the created board from the redirect URL
        $board = Board::where('name', 'My Test Board')->first();
        $this->assertNotNull($board);
        $this->assertEquals('My Test Board', $board->name);
        $this->assertEquals('This is a test board description', $board->description);
        $this->assertEquals($user->id, $board->user_id);
    }

    public function test_board_creation_creates_default_columns()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/boards', [
                'name' => 'Board with Default Columns',
                'description' => 'Testing default columns'
            ]);

        $response->assertRedirect();

        $board = Board::where('name', 'Board with Default Columns')->first();
        $this->assertNotNull($board);

        // Check that default columns were created
        $columns = $board->columns()->orderBy('position')->get();
        $this->assertCount(4, $columns);

        // Assert each column explicitly
        $this->assertEquals('To Do', $columns[0]->name);
        $this->assertEquals(0, $columns[0]->position);
        
        $this->assertEquals('In Progress', $columns[1]->name);
        $this->assertEquals(1, $columns[1]->position);
        
        $this->assertEquals('Testing', $columns[2]->name);
        $this->assertEquals(2, $columns[2]->position);
        
        $this->assertEquals('Done', $columns[3]->name);
        $this->assertEquals(3, $columns[3]->position);
    }

    public function test_board_creation_without_description()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/boards', [
                'name' => 'Board without Description'
            ]);

        $response->assertRedirect();

        $board = Board::where('name', 'Board without Description')->first();
        $this->assertNotNull($board);
        $this->assertNull($board->description);
    }

    public function test_board_creation_requires_authentication()
    {
        $response = $this->post('/boards', [
            'name' => 'Unauthorized Board',
            'description' => 'This should fail'
        ]);

        $response->assertRedirect('/login');
    }

    public function test_board_creation_validates_required_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/boards', [
                'description' => 'Missing name'
            ]);

        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasErrors(['name' => 'The board name is required.']);
    }

    public function test_board_creation_validates_name_max_length()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/boards', [
                'name' => str_repeat('a', 256), // Exceeds 255 character limit
                'description' => 'Name too long'
            ]);

        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasErrors(['name' => 'The board name may not be greater than 255 characters.']);
    }

    public function test_board_creation_validates_description_max_length()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/boards', [
                'name' => 'Valid Name',
                'description' => str_repeat('a', 1001) // Exceeds 1000 character limit
            ]);

        $response->assertSessionHasErrors(['description']);
        $response->assertSessionHasErrors(['description' => 'The board description may not be greater than 1000 characters.']);
    }

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
}
