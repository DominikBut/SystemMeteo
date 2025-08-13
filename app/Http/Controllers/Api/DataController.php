<?php

namespace App\Http\Controllers\Api;

use App\Models\Data;
use App\Models\Stations;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDataRequest;

class DataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDataRequest $request)
    {

        $station = Stations::where('id', $request->station_id)->where('active', true)->first();
        if (!$station) {
            return response()->json(['message' => 'Station not activated!'], 423);
        }
        // // Store
        Data::create([
            'station_id'     => $station->id, // FK to stations table
            'temp_air'       => $request->temp_air,
            'humidity'       => $request->humidity,
            'wind_speed'     => $request->wind_speed,
            'wind_direction' => $request->wind_direction,
            'rain_10min'     => $request->rain_10min,
        ]);

        // Save or process data linked to $user
        return response()->json(['message' => 'Data received successfully'], 201);
    }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
