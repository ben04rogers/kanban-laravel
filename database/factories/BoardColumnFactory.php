<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardColumn>
 */
class BoardColumnFactory extends Factory
{
    protected $model = BoardColumn::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'position' => $this->faker->numberBetween(0, 10),
            'board_id' => Board::factory(),
        ];
    }
}
