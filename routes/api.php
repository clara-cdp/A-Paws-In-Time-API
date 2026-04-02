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
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});


/*Route::middleware(['auth:api', 'role:' . RolesEnum::Admin->value])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index']);
});

Route::middleware(['auth:api', 'role:' . RolesEnum::User->value])->group(function () {
    Route::get('/user/profile', [UserController::class, 'show']);
});*/
