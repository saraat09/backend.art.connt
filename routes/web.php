<?php

use Illuminate\Support\Facades\Route;

// Page de garde
Route::get('/', function () {
    return response()->json([
        'app'     => 'ArtisanConnect API',
        'version' => '1.0.0',
        'status'  => 'running',
        'docs'    => '/api',
    ]);
});
