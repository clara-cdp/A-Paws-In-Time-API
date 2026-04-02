<?php

use App\Enums\RolesEnum;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//  Route::get('/user', function (Request $request) {
//      return $request->user();
//  })->middleware('auth:api');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);    
});

Route::middleware('auth:api')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [UserController::class, 'show']);
    Route::put('/me', [UserController::class, 'update']);
    Route::delete('/me',[UserController::class, 'destroy']);
});

Route::middleware(['auth:api', 'role:' . RolesEnum::Admin->value])
->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}',[AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::put('/users/{user}/block', [AdminUserController::class, 'block']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']); 
});


