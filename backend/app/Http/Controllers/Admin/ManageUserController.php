<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class ManageUserController extends Controller
{
    // Fetch all users
    public function index()
    {
        return User::with('role')->get();
    }

    // Register/Add new user
public function store(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,lawyer,staff',
            'status' => 'required|in:active,inactive',
        ]);

        // Hash the password
        $data['password'] = Hash::make($data['password']);

        // Create the user
        $user = User::create($data);

        // Assign role
        $user->role()->create(['role' => $data['role']]);

        // Determine who is performing the action
        $adminUser = auth()->user();
        $actorId = $adminUser ? $adminUser->id : null;
        $actorName = $adminUser ? "{$adminUser->first_name} {$adminUser->last_name}" : "Self-registration";

        // Log activity
        $agent = new \Jenssegers\Agent\Agent();
        ActivityLog::create([
            'user_id' => $actorId,
            'action' => "{$actorName} registered new user '{$user->first_name} {$user->last_name}' with role '{$data['role']}' and status '{$data['status']}'",
            'ip_address' => $request->ip(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->load('role'),
        ], 201);
    }

        // Update user
    public function update(Request $request, $id)
    {
        // Find the user with role
        $user = User::with('role')->findOrFail($id);

        // Validate input
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Store old values
        $oldValues = $user->only(['first_name', 'middle_name', 'last_name', 'email', 'status']);
        $oldRole = $user->role->role ?? null;

        // Prevent demoting the last admin
        if ($oldRole === 'admin' && $data['role'] !== 'admin') {
            $adminCount = User::whereHas('role', fn($q) => $q->where('role', 'admin'))->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot change role. At least one admin must exist.'
                ], 403);
            }
        }

        // Track password update
        $passwordUpdated = false;
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $passwordUpdated = true;
        } else {
            unset($data['password']);
        }

        // Update user
        $user->update($data);

        // Update role
        $user->role()->updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $data['role']]
        );

        // --- Activity Logging ---
        $changes = [];

        if ($oldRole !== $data['role']) {
            $changes[] = "role changed from '{$oldRole}' to '{$data['role']}'";
        }
        if ($oldValues['status'] !== $data['status']) {
            $changes[] = "status changed from '{$oldValues['status']}' to '{$data['status']}'";
        }
        if ($oldValues['first_name'] !== $data['first_name'] ||
            $oldValues['middle_name'] !== $data['middle_name'] ||
            $oldValues['last_name'] !== $data['last_name']) {
            $changes[] = "name changed from '{$oldValues['first_name']} {$oldValues['middle_name']} {$oldValues['last_name']}' 
    to '{$data['first_name']} {$data['middle_name']} {$data['last_name']}'";
        }
        if ($oldValues['email'] !== $data['email']) {
            $changes[] = "email changed from '{$oldValues['email']}' to '{$data['email']}'";
        }
        if ($passwordUpdated) {
            $changes[] = "password updated";
        }

        if (!empty($changes)) {
            $agent = new \Jenssegers\Agent\Agent();
            $adminUser = auth()->user();
            $actorId = $adminUser ? $adminUser->id : null;
            $actorName = $adminUser ? "{$adminUser->first_name} {$adminUser->last_name}" : "Self-update";

            ActivityLog::create([
                'user_id' => $actorId,
                'action' => "{$actorName} updated user '{$oldValues['first_name']} {$oldValues['middle_name']} {$oldValues['last_name']}':\n" 
                            . implode("\n", $changes),
                'ip_address' => $request->ip(),
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('role'),
        ]);
    }

public function destroy(Request $request, $id)
{
    $user = User::with('role')->findOrFail($id);

    if ($user->role->role === 'admin') {
        return response()->json([
            'message' => 'Cannot delete an admin user.'
        ], 403);
    }

    // Log activity before deletion (preserve in logs)
    $agent = new Agent();
    ActivityLog::create([
        'user_id' => auth()->id(), // the admin performing the deletion
        'action' => "Deleted user '{$user->first_name} {$user->last_name}' with role '{$user->role->role}' and status '{$user->status}'",
        'ip_address' => $request->ip(),
        'browser' => $agent->browser(),
        'platform' => $agent->platform(),
        'user_agent' => $request->header('User-Agent'),
    ]);

    // Delete the user (hard delete)
    $user->delete();

    return response()->json(['message' => 'User deleted successfully']);
}

}
