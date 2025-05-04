<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;

Route::get('/', function () {
    return response()->json([
        'message' => 'Todo Backend API is working successfully!',
        'status' => 'success'
    ], 200);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->prefix('todos')->group(function () {
    Route::get('/', [TodoController::class, 'index']);
    Route::post('/', [TodoController::class, 'store']);
    Route::put('{todo}', [TodoController::class, 'update']);
    Route::delete('{todo}', [TodoController::class, 'destroy']);
    Route::post('/reorder', [TodoController::class, 'reorder']);
});


