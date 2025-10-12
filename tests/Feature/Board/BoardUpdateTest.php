<?php

namespace Tests\Feature\Board;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_owner_can_update_board_properties()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => 'Updated Board Name',
                'description' => 'Updated description',
                'status' => 'completed',
            ]);

        $response->assertRedirect();

        $board->refresh();
        $this->assertEquals('Updated Board Name', $board->name);
        $this->assertEquals('Updated description', $board->description);
        $this->assertEquals('completed', $board->status);
    }

    public function test_board_owner_can_add_new_column()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        // Get existing columns
        $existingColumns = $board->columns()->orderBy('position')->get();

        $columnsData = $existingColumns->map(fn ($col) => [
            'id' => $col->id,
            'name' => $col->name,
            'position' => $col->position,
        ])->toArray();

        // Add new column
        $columnsData[] = [
            'id' => null,
            'name' => 'New Column',
            'position' => count($columnsData),
        ];

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertRedirect();

        $board->refresh();
        $this->assertCount(5, $board->columns); // 4 default + 1 new

        $newColumn = $board->columns()->where('name', 'New Column')->first();
        $this->assertNotNull($newColumn);
        $this->assertEquals(4, $newColumn->position);
    }

    public function test_board_owner_can_rename_existing_column()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columnToRename = $board->columns()->first();
        $originalId = $columnToRename->id;

        $columnsData = $board->columns()->orderBy('position')->get()->map(function ($col) use ($originalId) {
            return [
                'id' => $col->id,
                'name' => $col->id === $originalId ? 'Renamed Column' : $col->name,
                'position' => $col->position,
            ];
        })->toArray();

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertRedirect();

        $columnToRename->refresh();
        $this->assertEquals('Renamed Column', $columnToRename->name);
    }

    public function test_board_owner_can_delete_empty_column()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columnToDelete = $board->columns()->first();
        $columnId = $columnToDelete->id;

        // Create columns data without the first column (deleted)
        $columnsData = $board->columns()
            ->where('id', '!=', $columnId)
            ->orderBy('position')
            ->get()
            ->map(fn ($col, $idx) => [
                'id' => $col->id,
                'name' => $col->name,
                'position' => $idx,
            ])
            ->toArray();

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertRedirect();

        $board->refresh();
        $this->assertCount(3, $board->columns); // 4 default - 1 deleted
        $this->assertNull(BoardColumn::find($columnId)); // Column should be deleted
    }

    public function test_board_owner_cannot_delete_column_with_cards()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columnWithCards = $board->columns()->first();

        // Add a card to the column
        Card::factory()->create([
            'board_id' => $board->id,
            'board_column_id' => $columnWithCards->id,
            'title' => 'Test Card',
            'position' => 0,
        ]);

        // Try to delete column with cards
        $columnsData = $board->columns()
            ->where('id', '!=', $columnWithCards->id)
            ->orderBy('position')
            ->get()
            ->map(fn ($col, $idx) => [
                'id' => $col->id,
                'name' => $col->name,
                'position' => $idx,
            ])
            ->toArray();

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertSessionHasErrors(['columns']);
        $response->assertSessionHasErrorsIn('default', [
            'columns' => "Cannot delete column '{$columnWithCards->name}' because it contains 1 card(s). Please move or delete the cards first.",
        ]);

        // Column should still exist
        $this->assertNotNull(BoardColumn::find($columnWithCards->id));
    }

    public function test_board_owner_can_reorder_columns()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columns = $board->columns()->orderBy('position')->get();

        // Reverse the order
        $columnsData = $columns->reverse()->values()->map(fn ($col, $idx) => [
            'id' => $col->id,
            'name' => $col->name,
            'position' => $idx,
        ])->toArray();

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertRedirect();

        $board->refresh();
        $reorderedColumns = $board->columns()->orderBy('position')->get();

        // Check that order is reversed
        $this->assertEquals($columns[3]->name, $reorderedColumns[0]->name);
        $this->assertEquals($columns[2]->name, $reorderedColumns[1]->name);
        $this->assertEquals($columns[1]->name, $reorderedColumns[2]->name);
        $this->assertEquals($columns[0]->name, $reorderedColumns[3]->name);
    }

    public function test_board_owner_can_perform_multiple_column_operations()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columns = $board->columns()->orderBy('position')->get();

        $columnsData = [
            // Keep first column
            [
                'id' => $columns[0]->id,
                'name' => $columns[0]->name,
                'position' => 0,
            ],
            // Rename second column
            [
                'id' => $columns[1]->id,
                'name' => 'Renamed Column',
                'position' => 1,
            ],
            // Delete third column (omit from array)
            // Keep fourth column
            [
                'id' => $columns[3]->id,
                'name' => $columns[3]->name,
                'position' => 2,
            ],
            // Add new column
            [
                'id' => null,
                'name' => 'Brand New Column',
                'position' => 3,
            ],
        ];

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertRedirect();

        $board->refresh();
        $this->assertCount(4, $board->columns); // 4 default - 1 deleted + 1 new = 4

        // Verify rename
        $renamedColumn = $board->columns()->find($columns[1]->id);
        $this->assertEquals('Renamed Column', $renamedColumn->name);

        // Verify deletion
        $this->assertNull(BoardColumn::find($columns[2]->id));

        // Verify new column
        $newColumn = $board->columns()->where('name', 'Brand New Column')->first();
        $this->assertNotNull($newColumn);
    }

    public function test_shared_user_cannot_update_board_columns()
    {
        $owner = User::factory()->create();
        $sharedUser = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $owner->id]);

        // Share board with user
        $board->shares()->create(['user_id' => $sharedUser->id]);

        $columnsData = $board->columns()->orderBy('position')->get()->map(fn ($col) => [
            'id' => $col->id,
            'name' => $col->name,
            'position' => $col->position,
        ])->toArray();

        $columnsData[] = [
            'id' => null,
            'name' => 'New Column',
            'position' => count($columnsData),
        ];

        $response = $this->actingAs($sharedUser)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        // Shared users cannot update boards (only owners can)
        $response->assertForbidden();
    }

    public function test_update_validates_duplicate_column_names()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columnsData = $board->columns()->orderBy('position')->get()->map(fn ($col) => [
            'id' => $col->id,
            'name' => 'Duplicate Name', // All columns have same name
            'position' => $col->position,
        ])->toArray();

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertSessionHasErrors(['columns']);
        $response->assertSessionHasErrorsIn('default', [
            'columns' => 'Column names must be unique.',
        ]);
    }

    public function test_update_requires_at_least_one_column()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => [], // Empty columns array
            ]);

        $response->assertSessionHasErrors(['columns']);
        $response->assertSessionHasErrorsIn('default', [
            'columns' => 'At least one column is required.',
        ]);
    }

    public function test_update_validates_column_name_is_required()
    {
        $user = User::factory()->create();
        $board = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $columnsData = [
            [
                'id' => null,
                'name' => '', // Empty name
                'position' => 0,
            ],
        ];

        $response = $this->actingAs($user)
            ->put("/boards/{$board->id}", [
                'name' => $board->name,
                'description' => $board->description,
                'status' => $board->status,
                'columns' => $columnsData,
            ]);

        $response->assertSessionHasErrors(['columns.0.name']);
        $response->assertSessionHasErrorsIn('default', [
            'columns.0.name' => 'Column name is required.',
        ]);
    }

    public function test_update_validates_column_belongs_to_board()
    {
        $user = User::factory()->create();
        $board1 = Board::factory()->withColumns()->create(['user_id' => $user->id]);
        $board2 = Board::factory()->withColumns()->create(['user_id' => $user->id]);

        $board2Column = $board2->columns()->first();

        // Try to update board1 with a column from board2
        $columnsData = [
            [
                'id' => $board2Column->id, // Column from different board
                'name' => 'Test',
                'position' => 0,
            ],
        ];

        $response = $this->actingAs($user)
            ->put("/boards/{$board1->id}", [
                'name' => $board1->name,
                'description' => $board1->description,
                'status' => $board1->status,
                'columns' => $columnsData,
            ]);

        $response->assertSessionHasErrors(['columns']);
        $response->assertSessionHasErrorsIn('default', [
            'columns' => 'One or more columns do not belong to this board.',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_board()
    {
        $board = Board::factory()->create();

        $response = $this->put("/boards/{$board->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'status' => 'active',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_non_owner_cannot_update_board()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)
            ->put("/boards/{$board->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'status' => 'active',
            ]);

        $response->assertForbidden();
    }
}
