<?php

use App\Http\Controllers\generalMap;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get(
    '/map',
    [generalMap::class, 'showMap']
)->name('map');
Route::get('/stacja', function () {
    return view('stacja_history');
})->name('stacja');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
