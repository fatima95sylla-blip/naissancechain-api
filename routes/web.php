<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Documentation API Swagger/OpenAPI
 */
Route::get('/api/documentation', function () {
    return view('l5-swagger::index');
})->name('api.documentation');

Route::get('/api/docs', function () {
    return redirect()->route('api.documentation');
});
