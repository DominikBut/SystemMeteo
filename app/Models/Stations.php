<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stations extends Model
{
    use HasFactory;
    public $incrementing = false; // disable auto increment
    protected $keyType = 'string'; // primary key is a string

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                do {
                    // Generate 12-digit random number
                    $id = str_pad(random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
                } while (static::where($model->getKeyName(), $id)->exists());

                $model->{$model->getKeyName()} = $id;
            }
        });
    }

    protected $fillable = [
        'name',
        'lat',
        'lon',
        'voivodeship',
        'district',
        'photo',
        'user_id',
        'description',
        'temperature',
        'humidity',
        'wind',
        'rain',
        'active',
        'public',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function weatherData()
    {
        return $this->hasMany(Data::class, 'station_id');
    }
}
