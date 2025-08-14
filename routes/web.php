<?php

use App\Http\Controllers\generalMap;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/map', function () {
    return view('general_map');
})->name('map');

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
