<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Register, login, logout and password reset belong to the api's own users.
 * A key that has an auth URL gets its users from that auth service instead, so these calls are refused.
 */
class LocalUsersOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (app()->has('integration_auth_url') && app()->get('integration_auth_url')) {
            return response()->json(['message' => "This apikey uses an external auth service for its users.  Register, log in, log out and reset passwords there, then send its token as Authorization: Bearer <token>."], 409);
        }
        return $next($request);
    }
}
