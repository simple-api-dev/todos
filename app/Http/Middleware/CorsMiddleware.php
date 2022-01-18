<?php

namespace App\Http\Middleware;

class CorsMiddleware {

    public function handle($request, \Closure $next){

        error_log('CORS processing: ' . $request->getMethod());

	    $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Origin'
        ];

	    if ($request->isMethod('OPTIONS'))
        {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

         $response = $next($request);
         $response->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE');
         $response->header('Access-Control-Allow-Headers', '*');
         $response->header('Access-Control-Allow-Origin', '*');


         return $response;

//
//        $response = $next($request);
//        foreach($headers as $key => $value)
//        {
//            $response->header($key, $value);
//        }
//
//        error_log('cors: sending response');
//        return $response;
    }

}
