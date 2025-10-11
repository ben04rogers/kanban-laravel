<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardColumn;
use Illuminate\Support\Facades\DB;

class BoardService
{
    public function createBoard(string $userId, string $name, ?string $description = null): Board
    {
        $board = Board::create([
            'name' => $name,
            'description' => $description,
            'user_id' => $userId,
        ]);

        $this->createDefaultColumns($board);

        return $board;
    }

    public function updateBoard(Board $board, string $name, ?string $description = null, ?string $status = null, ?array $columns = null): Board
    {
        DB::transaction(function () use ($board, $name, $description, $status, $columns) {
            // Update board properties
            $updateData = [
                'name' => $name,
                'description' => $description,
            ];

            if ($status !== null) {
                $updateData['status'] = $status;
            }

            $board->update($updateData);

            // Update columns if provided
            if ($columns !== null) {
                $this->updateBoardColumns($board, $columns);
            }
        });

        return $board->fresh(['columns']);
    }

    /**
     * Update board columns (create, update, delete, reorder)
     * Handles all column operations in a single transaction
     *
     * @param Board $board
     * @param array $columnsData Array of column data with id (nullable), name, position
     * @return void
     */
    public function updateBoardColumns(Board $board, array $columnsData): void
    {
        $existingColumnIds = array_filter(array_column($columnsData, 'id'));

        // Delete columns that are not in the new list (validation already checked for cards)
        // Only delete if there are existing column IDs, otherwise whereNotIn([]) would delete all
        if (!empty($existingColumnIds)) {
            $board->columns()->whereNotIn('id', $existingColumnIds)->delete();
        } else {
            // If no existing IDs, all columns are new, so delete all old columns
            $board->columns()->delete();
        }

        // Process each column (create or update)
        foreach ($columnsData as $columnData) {
            if (empty($columnData['id'])) {
                // Create new column
                BoardColumn::create([
                    'board_id' => $board->id,
                    'name' => $columnData['name'],
                    'position' => $columnData['position'],
                ]);
            } else {
                // Update existing column
                BoardColumn::where('id', $columnData['id'])
                    ->where('board_id', $board->id)
                    ->update([
                        'name' => $columnData['name'],
                        'position' => $columnData['position'],
                    ]);
            }
        }
    }

    public function deleteBoard(Board $board): bool
    {
        return $board->delete();
    }

    public function reorderColumns(Board $board, array $columns): void
    {
        foreach ($columns as $columnData) {
            $board->columns()->where('id', $columnData['id'])->update([
                'position' => $columnData['position']
            ]);
        }
    }

    private function createDefaultColumns(Board $board): void
    {
        $defaultColumns = [
            ['name' => 'To Do', 'position' => 0],
            ['name' => 'In Progress', 'position' => 1],
            ['name' => 'Testing', 'position' => 2],
            ['name' => 'Done', 'position' => 3],
        ];

        foreach ($defaultColumns as $column) {
            BoardColumn::create([
                'name' => $column['name'],
                'position' => $column['position'],
                'board_id' => $board->id,
            ]);
        }
    }
}
