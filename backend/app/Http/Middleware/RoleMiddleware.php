<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  // role to check, e.g. "admin"
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user(); // same as auth()->user()

        // If user has no role, or role does not match, abort
        if (!$user || !$user->role || $user->role->role !== $role) {
            abort(403, 'Unauthorized: Insufficient role');
        }

        return $next($request);
    }
}
