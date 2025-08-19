<?php

namespace Database\Seeders;

use App\Models\Data;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Stations;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Make some stations
        // $stations = Stations::factory()->count(10)->create();
        $stations = Stations::get();


        // For each station, make some fake weather data
        $stations->each(function ($station) {
            Data::factory()->count(10)->create([
                'station_id' => $station->id
            ]);
        });
    }
}
