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

            //Check to see if the integration cookie has been set and if not then call the integration API
            $payload = json_decode($this->decrypt($request->cookie("integration")));
            //error_log(print_r($payload,true));
            $integration_id = $payload->id;
            $integration_email = $payload->email;
            $integration_premium = $payload->premium;

            //uncomment for debugging remote API call when cookie has been set:
            //            $integration_id = null;
            //            $integration_email = null;
            //            $integration_premium = false;

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
                        $integration_premium = array_key_exists("integration_premium",$integration) ? $integration["integration_premium"] : false;
                        error_log("Integration id: ". $integration_id);
                        error_log("integration Email: ". $integration_email);
                        error_log("integration Premium: " . $integration_premium);
                        app()->instance('integration_id', $integration_id);
                        app()->instance('integration_email', $integration_email);
                        app()->instance('integration_premium', $integration_premium);
                    }
                    else{
                        error_log(print_r($response->getStatusCode(),1));
                        $error = true;
                    }

                }
                catch(ConnectionException $ex){
                    $error = true;
                }
                if (!$error)
                    return $next($request);
                else
                {
                    $message = ['message' => "Request failed.  API Key not found."];
                    error_log("passing back 401");
                    return response()->json($message, 401);
                }
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

    private function decrypt ($data) :string{
        return convert_uudecode (base64_decode ($data));
    }


}
