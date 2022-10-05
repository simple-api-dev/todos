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

        if($integration_id != "") {
            //make a hash and save that
            $integration_id = app()->has('integration_id') ? app()->get('integration_id') : "";
            $integration_email = app()->has('integration_email') ? app()->get('integration_email') : "";
            $integration_premium = app()->has("integration_premium") ? app()-> get("integration_premium") : false;
            $payload = $this->encrypt(json_encode(array("id"=>$integration_id, "email"=>$integration_email,"premium"=>$integration_premium)));
            $cookie = Cookie::create('integration', $payload, time() + (60*60*6));
            $response->cookie($cookie);
        }

        return $response;
    }

    private function encrypt ($data) : string {
        return base64_encode (convert_uuencode ($data));
    }

}
