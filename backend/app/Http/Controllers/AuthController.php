<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;
use Jenssegers\Agent\Agent;

class AuthController extends Controller
{
public function register(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'role' => 'required|in:admin,lawyer,staff',
    ]);

    $user = User::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'middle_name' => $request->middle_name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $user->role()->create([
        'role' => $request->role
    ]);

    // --- Activity Logging ---
    $agent = new \Jenssegers\Agent\Agent();

    if (auth()->check()) {
        // Admin is creating this user
        $admin = auth()->user();
        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => "Admin '{$admin->first_name} {$admin->last_name}' registered new user '{$user->first_name} {$user->last_name}' with role '{$request->role}'",
            'ip_address' => $request->ip(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_agent' => $request->header('User-Agent'),
        ]);
    } else {
        // Self-registration
        ActivityLog::create([
            'user_id' => $user->id, // or null if you prefer
            'action' => "Registered new user '{$user->first_name} {$user->last_name}' with role '{$request->role}'",
            'ip_address' => $request->ip(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_agent' => $request->header('User-Agent'),
        ]);
    }
    // --- End Activity Logging ---

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user->load('role'),
        'token' => $token,
        'token_type' => 'Bearer'
    ], 201);
}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

     // Check if the user is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact the administrator.'
            ], 403);
        }
        // --- Activity Logging ---
        $agent = new Agent();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Logged in',
            'ip_address' => $request->ip(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        $user->touch(); // updates last login time

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role->role ?? null,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

public function logout(Request $request)
{
    $user = auth()->user();

    if ($user) {
        $agent = new Agent();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Logged out',
            'ip_address' => $request->ip(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        $user->currentAccessToken()?->delete();
    }

    return response()->json(['message' => 'Logged out successfully']);
}

}
