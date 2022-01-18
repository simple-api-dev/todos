<?php

namespace App\Http\Middleware;

use App\Models\Integration;
use Closure;
use Illuminate\Http\Request;

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

        if($request->has('apikey')){
            $key = $request->get('apikey');
            //$query = "SELECT * FROM integrations where apikey='{$key}'";
            $integration = Integration::where("apikey", $key)->where('enabled',true)->first();

            if ($integration) {
                if($integration->status != 'KEY_USED') {
                    $integration->status = 'KEY_USED';
                    $integration->save();
                }
                // Lumen does not support sessions, it's totally stateless, so we'll need to do this everytime
                app()->instance('integration_id', $integration->id);
                // all is good in the world, carry on
                return $next($request);
            }
            else{
                $message = ['message' => "Unauthorized.  Invalid apikey detected [{$key}] . Visit " . config("app.name") . "/get-key for your free apikey today."];
                return response()->json($message, 401);
            }

        }
        else {
            $message = ['message' => 'Unauthorized.  Please provide a valid apikey.  Visit " . config("app.name") . "/get-key for your free apikey today.'];
            return response()->json($message, 401);
        }
    }
}
