<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class generalMap extends Controller
{
    public function showMap(Request $request)
    {
        try {
            if (!Cache::has('DaneStacji')) {
                $response = Http::get('https://danepubliczne.imgw.pl/api/data/meteo/');
                if ($response->successful()) {
                    Cache::put('DaneStacji', json_decode($response->body()), now()->addMinutes(10));
                    $dane = Cache::get('DaneStacji');
                    $askTime = Carbon::now()->format('Y-m-d H:i:s');
                    Cache::put('AskTime', $askTime);
                    return view('general_map', [
                        'data' => $dane,
                        'status' => 'zapisano',
                        'AskedAt' => $askTime,
                    ]);
                } else {
                    // Handle the error
                    return view('Error', ['data' => 'Błąd połączenia']);
                }
            } else {
                $dane = Cache::get('DaneStacji');
                $askTime = Cache::get('AskTime');
                return view('general_map', [
                    'data' => $dane,
                    'status' => 'odczytano z api',
                    'AskedAt' => $askTime,
                ]);
            }
        } catch (\Throwable $th) {
            return view(
                'Error',
                ['data' => $th,]
            );
        }
    }
}
