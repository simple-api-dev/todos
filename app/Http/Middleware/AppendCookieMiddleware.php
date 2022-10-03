<?php
namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Cookie;

class AppendCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $integration_id = app()->has('integration_id') ? app()->get('integration_id') : "";
        $integration_email = app()->has('integration_email') ? app()->get('integration_email') : "";

        if($integration_id != "") {
            $cookie = Cookie::create('integration_id', $integration_id, time() + (60*60*6));
            $cookie = Cookie::create('integration_email', $integration_email, time() + (60*60*6));
            $response->cookie($cookie);
        }

        return $response;
    }
}
