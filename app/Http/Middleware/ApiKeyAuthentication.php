<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiKeyAuthentication
{
    private const CACHE_VALID_SECONDS = 300;
    private const CACHE_INVALID_SECONDS = 60;

    /**
     * Handle an incoming request.
     *
     * The apikey is validated against the shared keys service. The result is cached
     * server side, keyed by apikey. It used to be kept in a client cookie, but that
     * cookie was not signed, so anyone could forge it to act as another integration
     * or claim premium.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $key = $request->get('apikey');
        if (!is_string($key) || $key === '') {
            $message = ['message' => "Unauthorized.  Please provide a valid apikey.  Visit " . config("app.name") . "/get-key for your free apikey today."];
            return response()->json($message, 401);
        }

        $cache_key = 'integration:' . sha1($key . '|' . env('APP_URL'));
        $integration = Cache::get($cache_key);

        if ($integration === null) {
            try {
                $url = env("INTEGRATION_URL") . "?api=" . urlencode($key) . "&product_url=" . urlencode(env("APP_URL"));
                $response = Http::timeout(3)->get($url);

                if ($response->getStatusCode() == 200) {
                    $integration = json_decode($response->getBody(), true);
                    Cache::put($cache_key, $integration, self::CACHE_VALID_SECONDS);
                } else {
                    $integration = false;
                    Cache::put($cache_key, false, self::CACHE_INVALID_SECONDS);
                }
            } catch (ConnectionException $ex) {
                $integration = false;
            }
        }

        if (!$integration) {
            $message = ['message' => "Request failed.  API Key not found."];
            return response()->json($message, 401);
        }

        app()->instance('integration_id', $integration["integration_id"]);
        app()->instance('integration_email', $integration["integration_email"]);
        app()->instance('integration_premium', $integration["integration_premium"] ?? false);
        app()->instance('integration_auth_url', $integration["integration_auth_url"] ?? null);

        return $next($request);
    }
}
