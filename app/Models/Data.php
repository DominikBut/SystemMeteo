<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Data extends Model
{
    use HasFactory;
    protected $fillable = [
        'station_id',
        'temp_air',
        'humidity',
        'wind_speed',
        'wind_direction',
        'rain_10min',
    ];
    public function getuserStations()
    {
        return $this->belongsTo(Stations::class, 'station_id')->where('user_id', Auth::id());
    }
    public function getStation()
    {
        return $this->belongsTo(Stations::class, 'station_id');
    }
}
