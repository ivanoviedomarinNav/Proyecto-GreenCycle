<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TreeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/trees', [TreeController::class, 'index']);
    Route::get('/trees/{tree}', [TreeController::class, 'show']);
    Route::post('/trees', [TreeController::class, 'store']);
});
