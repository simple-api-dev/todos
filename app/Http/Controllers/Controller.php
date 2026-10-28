<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class Controller extends BaseController
{
    /**
     * @OA\Info(
     *   title="Todo API",
     *   version="1.0",
     *   description="This is an open and free to use API for developers to test their client projects.
        All api calls require you to pass in <br> your API Key with the querystring
        <b>?apikey=########-####-####-####-############</b>, <br><br>Please
        register for your own API Key today at https://todoapi.net/register
        <br><br><b>Optional: use your own auth service.</b> By default this API has its own users. If you already have an auth service, or you use the SimpleAPI Auth API (<a href='https://auth.simpleapi.dev'>auth.simpleapi.dev</a>), point your key at it with <b>POST https://keys.simpleapi.dev/api/keys/auth-url</b>. The user calls (/users/...) then accept that service's tokens, and register, login, logout and password reset here answer 409.",
     *
     * )
     * @OA\SecurityScheme(
     *     type="http",
     *     description="Login with email and password to get the authentication token",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     securityScheme="apiAuth",
     * )
     */
    protected bool $secure_mode;
    protected string $integration_id;
    protected $current_user;
    protected string $app_name;
    protected bool $is_admin = false;

    public function __construct(Request $request)
    {
        $this->integration_id = app()->has('integration_id') ? app()->get('integration_id') : "";
        $this->integration_email = app()->has('integration_email') ? app()->get('integration_email') :"";
        $this->integration_premium = app()->has('integration_premium') ? app()->get('integration_premium') : false;
        $this->secure_mode = app()->has('secure_mode') ? app()->get('secure_mode') : false;
        $this->current_user = app()->has('current_user') ? app()->get('current_user') : null;
        $this->app_name = $this->secure_mode ? "secure." . config("app.name") : config("app.name");
        $this->is_admin = app()->has('is_admin') ? app()->get('is_admin') : false;

        try {
            $analytics = new Analytics(true);
            $analytics
                ->setProtocolVersion('1')
                ->setHitType("pageview")
                ->setTrackingId(env('GOOGLE_ANALYTICS'))
                ->setClientId($this->integration_id ?? 'new')
                ->setDocumentPath($request->path())
                ->setDocumentTitle($request->path());

            $analytics->sendPageview();
        }
        catch (Exception $ex){
            error_log($ex->getMessage());
        }
    }

}
