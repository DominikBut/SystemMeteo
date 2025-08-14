<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Stations;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stations>
 */
class StationsFactory extends Factory
{
    protected $model = Stations::class;

    public function definition(): array
    {
        $districts = [
            'bielski',
            'ciechanowski',
            'garwoliński',
            'gostyniński',
            'grodziski',
            'grójecki',
            'kozanski',
            'legionowski',
            'lipski',
            'lowicki',
            'makowski',
            'miedzyrzec_podlaski',
            'ostrolecki',
            'ostrowski',
            'ostrowiecki',
            'piaseczynski',
            'płocki'
        ];

        $voivodeships = [
            'dolnoslaskie',
            'kujawsko-pomorskie',
            'lubelskie',
            'lubuskie',
            'lodzkie',
            'malopolskie',
            'mazowieckie',
            'opolskie',
            'podkarpackie',
            'podlaskie',
            'pomorskie',
            'slaskie',
            'swietokrzyskie',
            'warminsko-mazurskie',
            'wielkopolskie',
            'zachodniopomorskie'
        ];

        return [
            // 12-digit random number as a string
            'id' => (string) random_int(100000000000, 999999999999),

            // Link to a random user if any exist
            'user_id' => User::inRandomOrder()->first()?->id,

            'name' => $this->faker->city . ' Weather Station',
            'lat' => $this->faker->latitude(49, 55),
            'lon' => $this->faker->longitude(14, 24),
            'voivodeship' => $this->faker->randomElement($voivodeships),
            'district' => $this->faker->randomElement($districts),

            // Fake placeholder photo
            'photo' => $this->faker->imageUrl(640, 480, 'nature', true, 'station'),

            'description' => $this->faker->sentence(12),
            'temperature' => $this->faker->boolean(90),
            'humidity' => $this->faker->boolean(90),
            'wind' => $this->faker->boolean(90),
            'rain' => $this->faker->boolean(90),
            'active' => $this->faker->boolean(95),
            'public' => $this->faker->boolean(95),
        ];
    }
}
