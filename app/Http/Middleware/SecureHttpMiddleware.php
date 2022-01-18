<?php

namespace App\Http\Middleware;

use App\Models\Integration;
use Closure;
use Illuminate\Http\Request;

class SecureHttpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        //  To support listening on all iterations of local ip, use:
        //  php -S 0.0.0.0:8000 public/index.php
        // and add the domains (secure.todoapi.net and todoapi.net) to your system32/drivers/etc/hosts file
        // in an admin terminal window

        $host_parts = explode("/", $request->path());
        error_log($request->path());
        if(sizeof($host_parts) > 0) {
            $position = in_array("api",$host_parts) ? 1: 0;
            $secure = explode("/", $request->path())[$position] == "users";
            app()->instance('secure_mode', $secure);
        }
        // we need to authenticate u

        return $next($request);
    }
}
