<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ----------------------
// Authentication
// ----------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// ----------------------
// Authenticated user info
// ----------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

// ----------------------
// Admin routes
// ----------------------
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', fn() => response()->json([
        'message' => 'Admin dashboard',
    ]));
    Route::get('/admin/users', fn() => response()->json([
        'message' => 'Admin users list',
    ]));
});

// ----------------------
// Staff routes
// ----------------------
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', fn() => response()->json([
        'message' => 'Staff dashboard',
    ]));
    Route::get('/staff/clients', fn() => response()->json([
        'message' => 'Staff clients list',
    ]));
});

// ----------------------
// Lawyer routes
// ----------------------
Route::middleware(['auth:sanctum', 'role:lawyer'])->group(function () {
    Route::get('/lawyer/dashboard', fn() => response()->json([
        'message' => 'Lawyer dashboard',
    ]));
    Route::get('/lawyer/cases', fn() => response()->json([
        'message' => 'Lawyer cases list',
    ]));
});
