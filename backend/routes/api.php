<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Api\ScheduleController;

Route::middleware('auth:sanctum')->group(function () {

    // Schedules CRUD
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    Route::put('/schedules/{id}', [ScheduleController::class, 'update']);
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
    
    Route::get('/lawyers', [ScheduleController::class, 'lawyers']);

    // Admin dashboard schedules
    Route::get('/scheduled', [ScheduleController::class, 'upcomingSchedules']);
});



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
