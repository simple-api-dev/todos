<?php

namespace App\Http\Middleware;

class OriginCheckMiddleware {

    public function handle($request, \Closure $next)
    {
        //error_log('Accessing origin check middleware');
        $requestHost = parse_url($request->headers->get('origin'),  PHP_URL_HOST);
        if(!$requestHost || $requestHost == config("app.name") || $requestHost == "localhost" || $requestHost == "todos.simpleapi.dev"){
            return $next($request);
        }
        else{
            return response()->json(["message"=>"Invalid Origin.  Unauthorized."], 403);
        }
    }

}
