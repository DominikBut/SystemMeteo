<?php

namespace Database\Seeders;

use App\Models\Data;
use App\Models\Stations;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DataSeeder2hnow extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        //Make some stations
        //$stations = Stations::factory()->count(25)->create();
        $stations = Stations::get();


        //For each station, make some fake weather data
        $stations->each(function ($station) {
            Data::factory()->count(20)->create([
                'station_id' => $station->id,
                'created_at' => fake()->dateTimeBetween('-2 hours', 'now'),
                'updated_at' => now(),
            ]);
        });
    }
}
