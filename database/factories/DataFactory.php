<?php

namespace Database\Factories;

use App\Models\Data;
use App\Models\Stations;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Data>
 */
class DataFactory extends Factory
{
    protected $model = Data::class;

    public function definition(): array
    {
        return [
            'station_id' => Stations::inRandomOrder()->first()?->id,
            'temp_air' => $this->faker->randomFloat(2, 10, 30),
            'humidity' => $this->faker->randomFloat(2, 60, 100),
            'wind_speed' => $this->faker->optional(0.90)->randomFloat(2, 0, 15),
            'wind_direction' => $this->faker->optional(0.90)->randomFloat(2, 0, 360),
            'rain_10min' => $this->faker->optional(0.5)->randomFloat(2, 0, 0.90),
            'created_at' => $this->faker->dateTimeBetween('-50 days', 'now'),
            'updated_at' => now(),
        ];
    }
}
