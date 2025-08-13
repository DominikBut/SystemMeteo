<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $fillable = [
        'station_id',
        'temp_air',
        'humidity',
        'wind_speed',
        'wind_direction',
        'rain_10min',
    ];
    public function getStation()
    {
        return $this->belongsTo(Stations::class, 'station_id')->where('user_id', Auth::id());
    }
}
