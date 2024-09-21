<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WaterJugController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/water-jugs', [WaterJugController::class, 'resolve']);

Route::get('/example', function () {
    return response()->json(['message' => 'BB francys API route is working']);
});