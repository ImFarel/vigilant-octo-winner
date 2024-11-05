<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatroomController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chatrooms', [ChatroomController::class, 'store']);
    Route::get('/chatrooms', [ChatroomController::class, 'index']);
    Route::post('/chatrooms/{chatroom}/enter', [ChatroomController::class, 'enter']);
    Route::post('/chatrooms/{chatroom}/leave', [ChatroomController::class, 'leave']);

    Route::post('/chatrooms/{chatroom}/messages', [MessageController::class, 'store']);
    Route::get('/chatrooms/{chatroom}/messages', [MessageController::class, 'index']);
});