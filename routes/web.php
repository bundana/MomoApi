<?php

use App\Http\Controllers\MoMoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::controller(MoMoController::class)->group(function () {
    Route::get('/apiuser', 'createApiUser');

    Route::get('/user/profile', function () {
        // Uses first & second middleware...
    });
});
