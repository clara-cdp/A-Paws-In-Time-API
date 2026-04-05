<?php

use App\Enums\RolesEnum;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameActionController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    //Public Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);    
});

Route::middleware('auth:api')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);

    //Profile Routes
    Route::get('/me', [UserController::class, 'show']);
    Route::put('/me', [UserController::class, 'update']);
    Route::delete('/me',[UserController::class, 'destroy']);

    // Game Routes
    Route::get('/games', [GameController::class, 'index']);      
    Route::post('/games', [GameController::class, 'store']);
    Route::get('/games/{game}', [GameController::class, 'show']);   //shows one game 
    Route::put('/games/{game}', [GameController::class, 'update']);  //saves / updates a game
    Route::delete('/games/{game}', [GameController::class, 'destroy']);

    //Play Routes
    Route::post('/games/{game}/actions', [GameActionController::class, 'play']); //play game
});

    //Admin Routes
Route::middleware(['auth:api', 'role:' . RolesEnum::Admin->value])
->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}',[AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::put('/users/{user}/block', [AdminUserController::class, 'block']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']); 
});


