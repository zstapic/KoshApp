<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PlayerController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Povezano sa serverom', // Promijeniti kada je potrebna prava poruka od servera
        'time' => now()->toIso8601String(),
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/clubs', [ClubController::class, 'index']);
Route::post('/clubs', [ClubController::class, 'store']);
Route::get('/clubs/{club}', [ClubController::class, 'show']);
Route::put('/clubs/{club}', [ClubController::class, 'update']);
Route::delete('/clubs/{club}', [ClubController::class, 'destroy']);

// Dobne kategorije (KOSH-48) – x-roles: [admin]
// TODO login: grupi dodati ->middleware(['auth:sanctum', 'role:admin'])
Route::group([], function () {
    Route::apiResource('categories', CategoryController::class);
});
Route::get('/players', [PlayerController::class, 'index']);
Route::post('/players', [PlayerController::class, 'store']);
Route::get('/players/{player}', [PlayerController::class, 'show']);
Route::put('/players/{player}', [PlayerController::class, 'update']);
Route::delete('/players/{player}', [PlayerController::class, 'destroy']);
