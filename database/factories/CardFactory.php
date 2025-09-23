<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'position' => $this->faker->numberBetween(0, 10),
            'board_id' => Board::factory(),
            'board_column_id' => BoardColumn::factory(),
            'user_id' => $this->faker->optional(0.8)->passthrough(User::factory()),
        ];
    }

    /**
     * Create a card with specific board and column
     */
    public function forBoardAndColumn(Board $board, BoardColumn $column): static
    {
        return $this->state(fn (array $attributes) => [
            'board_id' => $board->id,
            'board_column_id' => $column->id,
        ]);
    }

    /**
     * Create a card assigned to a specific user
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a card with no assigned user
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Create a card with a specific position
     */
    public function atPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }
}
