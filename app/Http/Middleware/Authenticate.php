<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Exception;

class Authenticate
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, $guard = null)
    {
        try {
            if ($this->auth->guard($guard)->guest()) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Unauthorized: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}