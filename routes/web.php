<?php

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\generalMap;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/map', function () {
    return view('general_map');
})->name('map');

Route::get('/map/community', function () {
    return view('community_map');
})->name('map_community');

Route::get('/stations/recent', function () {
    return view('stacja_recent');
})->name('stacja_recent');

Route::get('/stations/official', function () {
    return view('stacja_history');
})->name('stacja_archive');

Route::get('/stations/community', function () {
    return view('stacja_community');
})->name('stacja_community');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
Route::get('/about', function () {
    return view('about');
})->name('about');


Route::get('/seed-24h', function () {
    if (request()->get('key') !== 'dominik') {
        abort(403, 'Unauthorized');
    }

    // Run specific seeder
    Artisan::call('db:seed', [
        '--class' => 'DataSeeder2hnow',
        '--force' => true, // force in production
    ]);

    $output = Artisan::output();

    return response(
        "<p>Wykonano " . now() . "</p>\n\n<br><pre>$output</pre>"
    );
});

Route::get('/user-emails', function () {
    if (request()->get('key') !== 'dominik') {
        abort(403, 'Unauthorized');
    }

    $emails = User::where('email', 'like', '%@example.%')
        ->pluck('email');

    return response()->json([
        'Domyślne hasło do kont' => 'test123',
        'Liczba publicznych kont' => $emails->count(),
        'E-maile' => $emails,
    ]);
});
