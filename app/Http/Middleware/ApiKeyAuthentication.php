<?php

namespace App\Http\Middleware;

use App\Models\Integration;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Cookie;

class ApiKeyAuthentication
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
        error_log("handle request");
        if($request->has('apikey')){
            $key = $request->get('apikey');
            //$query = "SELECT * FROM integrations where apikey='{$key}'";
            //Check to see if the integration_id cookie has been set and if not then call the integration API
            //$integration_id = $request->cookie('integration_id');
            //$integration_email = $request->cookie('integration_email');
            $integration_id = null;
            $integration_email = null;

            if(!$integration_id) {
                try {
                    $error = false;
                    $url = env("INTEGRATION_URL") . "?api=" . $key . "&product_url=" . env("APP_URL");
                    error_log("requesting api validation from ". $url);
                    $response = Http::timeout(3)->get($url);

                    if($response->getStatusCode() == 200){
                        $integration = json_decode($response->getBody(),true);
                        $integration_id = $integration["integration_id"];
                        $integration_email = $integration["integration_email"];
                        error_log("Integration id: ". $integration_id);
                        error_log("integration Email: ". $integration_email);
                        app()->instance('integration_id', $integration_id);
                        app()->instance('integration_email', $integration_email);
                    }
                    else{
                        error_log(print_r($response->getStatusCode(),1));
                        $error = true;
                    }

                }
                catch(ConnectionException $ex){
                    $error = true;
                }
                return $next($request);
            }
            else{
                app()->instance('integration_id', $integration_id);
                app()->instance('integration_email', $integration_email);
                // all is good in the world, carry on
                return $next($request);
            }

        }
        else {
            $message = ['message' => "Unauthorized.  Please provide a valid apikey.  Visit " . config("app.name") . "/get-key for your free apikey today."];
            return response()->json($message, 401);
        }
    }


}
