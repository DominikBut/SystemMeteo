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

Route::get('/station/recent', function () {
    return view('stacja_recent');
})->name('stacja_recent');

Route::get('/station/archive', function () {
    return view('stacja_archive');
})->name('stacja_archive');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
