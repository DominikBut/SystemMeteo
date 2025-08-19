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
            'temp_air' => $this->faker->optional(0.9)->randomFloat(2, -15, 35),
            'humidity' => $this->faker->optional(0.9)->randomFloat(2, 20, 100),
            'wind_speed' => $this->faker->randomFloat(2, 0, 15),
            'wind_direction' => $this->faker->randomFloat(2, 0, 360),
            'rain_10min' => $this->faker->randomFloat(2, 0, 5),
            'created_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'updated_at' => now(),
        ];
    }
}
