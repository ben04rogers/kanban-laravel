<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardColumn;

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

    public function updateBoard(Board $board, string $name, ?string $description = null, ?string $status = null): Board
    {
        $updateData = [
            'name' => $name,
            'description' => $description,
        ];

        if ($status !== null) {
            $updateData['status'] = $status;
        }

        $board->update($updateData);

        return $board->fresh();
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
