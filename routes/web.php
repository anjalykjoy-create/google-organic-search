<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/search', function () {
    return view('search');
});

Route::post('/search', [SearchController::class, 'search']);