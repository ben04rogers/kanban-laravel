<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Board>
 */
class BoardFactory extends Factory
{
    protected $model = Board::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'user_id' => User::factory(),
            'status' => 'active',
        ];
    }

    /**
     * Create a board with default columns.
     */
    public function withColumns(): static
    {
        return $this->afterCreating(function (Board $board) {
            $defaultColumns = [
                ['name' => 'To Do', 'position' => 0],
                ['name' => 'In Progress', 'position' => 1],
                ['name' => 'Testing', 'position' => 2],
                ['name' => 'Done', 'position' => 3],
            ];

            foreach ($defaultColumns as $column) {
                $board->columns()->create($column);
            }
        });
    }
}
