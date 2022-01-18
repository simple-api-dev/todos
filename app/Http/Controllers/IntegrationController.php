<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class IntegrationController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/keys/register",
     * summary="Register for new api key",
     * operationId="IntegrationController.Register",
     * tags={"Integrations"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass email and captcha",
     *    @OA\JsonContent(
     *       required={"email","captcha"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="captcha", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Registration successful",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="We have emailed you your api key.")
     *        )
     *     )
     * )
     */
    public function register(Request $request) : JsonResponse{

       $this->validate($request, [
            'email' => 'required|email',
            'captcha' => 'required|string'
        ]);

        // check captcha
        if(!$this->checkCaptcha($request->captcha)){
            $message = ['message' => "Invalid Captcha"];
            return response()->json($message, 400);
        }

        //check enabled accounts
        $email_exists_for_this_integration =
            Integration::where("email",$request->email)->where("enabled", true)->first();

        if($email_exists_for_this_integration){
            $message = ['message' => "Developer registration failed, email already registered to this developer account."];
            return response()->json($message, 400);
        }

        $integration = Integration::make([
            "email" => $request->email,
            "enabled" => true,
            "apikey" => User::v4()
        ]);

        $integration->save();
        // TODO: need to do email to send out apikey that way.
        try {
            Mail::send('mail',
                array(
                    "type" => "Registration",
                    "apikey" => $integration->apikey,
                    "doc_url" => env('APP_URL', '')
                ), function ($message) use ($request) {
                    $message->to($request->email, $request->email)->subject('ApiKey Registration');
                });
            $integration->status = 'MAIL_SENT';
            $integration->save();
            return response()->json(['message' => "Developer registration successful, API key has been emailed."]);
        }
        catch (\Exception $ex){
            $message = ['message' => $ex->getMessage()];
            return response()->json($message, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/keys/forgot",
     * summary="Resend apikey to email on file",
     * operationId="IntegrationController.Forgot",
     * tags={"Integrations"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass email and captcha",
     *    @OA\JsonContent(
     *       required={"email","captcha"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="captcha", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Registration successful",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="We have emailed you your api key again.")
     *        )
     *     )
     * )
     */
    public function forgot(Request $request) : JsonResponse{

        $this->validate($request, [
            'email' => 'required|email',
            'captcha' => 'required|string'
        ]);

        // check captcha
        if(!$this->checkCaptcha($request->captcha)){
            $message = ['message' => "Invalid Captcha"];
            return response()->json($message, 400);
        }

        $integration =
            Integration::where("email",$request->email)->where("enabled",true)->first();

        if($integration){
            try {
                Mail::send('mail',
                    array(
                        "type" => "Retrieval",
                        "apikey" => $integration->apikey,
                        "doc_url" => env('APP_URL', '')
                    ), function ($message) use ($request) {
                        $message->to($request->email, $request->email)->subject('ApiKey Retrieval Request');
                    });
                $message = ['message' => "Email address found, we have sent the apikey there.  Please check junk and spam if you can't seem to locate it."];
                return response()->json($message, 200);
            }
            catch (\Exception $ex){
                $message = ['message' => $ex->getMessage()];
                return response()->json($message, 500);
            }
        }
        else{
            $message = ['message' => "Email address not found."];
            return response()->json($message, 404);
        }

    }

    /**
     * @OA\Post(
     * path="/api/keys/deregister",
     * summary="Delete your developer account",
     * operationId="IntegrationController.Deregister",
     * tags={"Integrations"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass email and code",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="code", type="string", example="23445"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Deactivation request received",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="If code not provided, we have emailed you code to complete account deletion.")
     *        )
     *     )
     * )
     */
    public function deregister(Request $request) : JsonResponse{

        $this->validate($request, [
            'email' => 'required|email',
            'code' => 'string|max:6',
            'captcha' => 'required|string'
        ]);

        // check captcha
        if(!$this->checkCaptcha($request->captcha)){
            $message = ['message' => "Invalid Captcha"];
            return response()->json($message, 400);
        }

        if($request->has("code")){
            $integration_to_archive =
                Integration::where("email",$request->email)->where("code", $request->code)->where("enabled", true)->first();

            if($integration_to_archive){
                // delete apikey,code and mark account as disabled.  Keep record forever.
                $integration_to_archive->apikey = "";
                $integration_to_archive->enabled = false;
                $integration_to_archive->code = null;
                $integration_to_archive->deleted_at = date("Y-m-d H:i:s");
                $integration_to_archive->save();

                return response()->json(["message"=>"Your developer account has been deleted."]);
            }
            else{
                $message = ['message' => "Developer account deletion request failed, code not valid."];
                return response()->json($message, 404);
            }
        }

        $email_exists_for_this_integration =
            Integration::where("email",$request->email)->where("enabled",true)->first();

        if(!$email_exists_for_this_integration){
            $message = ['message' => "User account deletion request failed, email not found."];
            return response()->json($message, 404);
        }

        // TODO: need to do email with a code that user can type in to confirm deletion
        $email_exists_for_this_integration->code = strval(random_int(11111,99999));
        $email_exists_for_this_integration->save();
        try {
            Mail::send('mail',
                array(
                    "type"=>"Deletion Request",
                    "apikey"=>$email_exists_for_this_integration->code,
                    "doc_url"=>env('APP_URL', '')
                ), function($message) use ($request) {
                    $message->to($request->email, $request->email)->subject('ApiKey Account Deletion Code');
                });
            // use code 201 so ui can distinguish between the 2 success outcomes
            return response()->json(["message" => "We have emailed you a code to confirm deletion"], 201);
        }
        catch (\Exception $ex){
            $message = ['message' => $ex->getMessage()];
            return response()->json($message, 500);
        }
    }

    private function checkCaptcha(string $captcha) : bool {
        $secretKey = env('SECRET_CAPTCHA');
//        if($captcha == "boat2121"){
//            return true;
//        }

        // post request to server
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .
            '&response=' . urlencode($captcha);
        $response = file_get_contents($url);
        $responseKeys = json_decode($response,true);
        // should return JSON with success as true
        if($responseKeys["success"])
            return true;

        return false;
    }

}
