<?php

namespace App\Http\Controllers\Api;

use App\Models\Data;
use App\Models\Stations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

        $station = Stations::where('id', $request->station_id)
            ->first();

        if ($station->user_id != Auth::id()) {
            return response()->json(['message' => 'Not authorized to do this!'], 403);
        }
        if ($station->active == false) {
            return response()->json(['message' => 'Station not activated!'], 403);
        }
        // // Store
        Data::create([
            'station_id'     => $station->id,
            'temp_air'       => ($station->temperature == true ? $request->temp_air : null),
            'humidity'       => ($station->humidity == true ? $request->humidity : null),
            'wind_speed'     => ($station->wind == true ? $request->wind_speed : null),
            'wind_direction' => ($station->wind == true ? $request->wind_direction : null),
            'rain_10min'     => ($station->rain == true ? $request->rain_10min : null),
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
