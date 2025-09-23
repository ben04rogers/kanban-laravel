<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardShare>
 */
class BoardShareFactory extends Factory
{
    protected $model = BoardShare::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Create a board share for specific board and user
     */
    public function forBoardAndUser(Board $board, User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'board_id' => $board->id,
            'user_id' => $user->id,
        ]);
    }
}
