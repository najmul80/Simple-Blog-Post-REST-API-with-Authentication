<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::apiResource('posts', PostController::class)->only('index', 'show');

    // Authenticated (create, update, delete)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('posts', PostController::class)->except('index', 'show');
    });
});
