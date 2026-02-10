<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ActivityLogController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    });



Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users', [ManageUserController::class, 'index']);
    Route::put('/users/{id}', [ManageUserController::class, 'update']);
    Route::delete('/users/{id}', [ManageUserController::class, 'destroy']);
    Route::post('/addusers', [ManageUserController::class, 'store']);
});
