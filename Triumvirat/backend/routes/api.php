<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Api\AuthController;

//  ROUTES PUBLIQUES Test API
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'L\'API fonctionne parfaitement !',
        'timestamp' => now()
    ]);
});

// Routes publiques (SPA)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1'); // 3 tentatives de connexion par minute
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1'); // Limiter les inscriptions pour éviter les abus


// Routes protégées Sanctum
Route::middleware('auth:sanctum')->group(function () {
    //  Authentification & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::post('/check-password', [AuthController::class, 'checkPassword'])->middleware('auth:sanctum');

    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
    Route::get('users/{id}/posts', [PostController::class, 'getUserPosts']);

    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);

    //  Ressources standards
    Route::apiResource('users', UserController::class)->except(['store']);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('comments', CommentController::class)->only(['update', 'destroy']);
    Route::apiResource('media', MediaController::class);
    Route::middleware('auth:sanctum')->group(function () {

        Route::delete('/user', [UserController::class, 'destroy']);
    });
});