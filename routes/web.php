<?php

use App\Http\Controllers\MoMoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::controller(MoMoController::class)->group(function () {
    Route::get('/apiuser', 'createApiUser');
    Route::get('/apiuser/{id}', 'getApiUser');
    Route::get('/apiuser/{XReferenceId}/apikey', 'createApiKey')->name('api-key');


    Route::get('test', function (){
        return \route('api-key', 'ss');
    });
});
