<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Filament\Support\Colors\Color;
use App\Http\Requests\StoreDataRequest;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        RateLimiter::for('data-ip', function (Request $request) {
            return [
                Limit::perMinutes(40, 2)->by(
                    optional($request->user())->currentAccessToken()->id
                        ?: $request->ip()
                ),
            ];
        });


        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Blue,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);
    }
}
