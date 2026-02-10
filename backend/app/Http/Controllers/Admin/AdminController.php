<?php

// namespace App\Http\Controllers\Admin;

// use Illuminate\Http\Request;
// use App\Models\User;

// class AdminController extends Controller
// {
//     public function logoutUser(User $user)
//     {
//         // Delete all active tokens for this user
//         $user->tokens()->delete();

//         // Optional: Log this action in activity logs
//         \DB::table('activity_logs')->insert([
//             'user_id' => auth()->id(), // admin performing logout
//             'action' => "Logged out user: {$user->email}",
//             'ip_address' => request()->ip(),
//             'browser' => request()->header('User-Agent'),
//             'platform' => null,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         return response()->json(['message' => "User {$user->email} logged out successfully"]);
//     }
// }
