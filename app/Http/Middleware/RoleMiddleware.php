<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role The role to check for (e.g., 'admin')
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        // Ambil user langsung dari request. 
        $user = $request->user();

        // Jika tidak ada user (tamu), langsung tolak.
        if (!$user) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        // Cek relasi 'role' dan nama rolenya.
        if (!$user->role || $user->role->name !== $role) {
            return response()->json(['error' => 'Forbidden. You do not have the required role.'], 403);
        }

        return $next($request);
    }
}