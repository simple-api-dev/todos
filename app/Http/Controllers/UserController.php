<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/users/login",
     * summary="User login ",
     * operationId="User.login",
     * tags={"User"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass email and password",
     *    @OA\JsonContent(
     *       required={"email", "password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Login request successful",
     *    @OA\JsonContent(
     *     @OA\Property(property="token", type="string", example="fef6b64d-2223-5dec-83bf-c2dcd468500d"),
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(property="enabled", type="boolean"),
     *     @OA\Property(property="admin", type="boolean"),
     *        )
     *     )
     * )
     */
    /*
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function login(Request $request) : JsonResponse{

        if(!$this->secure_mode){
            $message = ['message' => "Login failed, please use users endpoint."];
            return response()->json($message, 400);
        }

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $user = User::where([
            ["email", $request->email],
            ["integration_id", $this->integration_id]
        ])->first();

        if($user && Hash::check($request->password, $user->password)){
            $user->token = User::v4();
            $user->save();
            return response()->json($user);
        }
        else{
            $message = ['message' => "Login Failed ({$request->email})"];
            return response()->json($message, 404);
        }
    }

    /**
     * @OA\Post(
     * path="/api/users/logout",
     * summary="User logout",
     * operationId="User.logout",
     * tags={"User"},
     * security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * @OA\Response(
     *    response=200,
     *    description="Logout successful",
     *)
     * )
     */
    public function logout(Request $request) : JsonResponse{

        if(!$this->secure_mode){
            $message = ['message' => "Logout failed, please use users endpoint."];
            return response()->json($message, 400);
        }

        if(!$this->current_user){
            $message = ['message' => "Logout failed, you are currently not logged in."];
            return response()->json($message, 403);
        }

        $this->current_user->token = null;
        $this->current_user->save();
        return response()->json(true);
    }

    /**
     * @OA\Post(
     * path="/api/users/register",
     * operationId="User.register",
     * summary="User registration. There's a max limit of 10 users per developer account.",
     * tags={"User"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Submit an email and password",
     *    @OA\JsonContent(
     *       required={"email", "name", "password"},
     *     @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="password", type="string", format="password", example="password"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Login request successful",
     *    @OA\JsonContent(
     *     @OA\Property(property="token", type="string", example="fef6b64d-2223-5dec-83bf-c2dcd468500d"),
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(property="enabled", type="boolean"),
     *     @OA\Property(property="admin", type="boolean"),
     *        )
     *     )
     * )
     */
    public function register(Request $request) : JsonResponse{

        if(!$this->secure_mode){
            $message = ['message' => "User registration failed, please use users endpoint."];
            return response()->json($message, 400);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:72'
        ]);

        $email_exists_for_this_integration =
            User::where([["email",$request->email],["integration_id", $this->integration_id]])->first();

        if($email_exists_for_this_integration){
            $message = ['message' => "User registration failed, email already registered to this developer account."];
            return response()->json($message, 400);
        }

        // Limit the total users per account
        $limit_reached = User::where('integration_id',$this->integration_id)->count() > 10;

        if($limit_reached && !$this->integration_premium){
            $message = "Maximum number of user accounts (10) per developer account has been reached. (Basic Acct)";
            return response()->json(['message' => $message], 403);
        }

        $user = User::make([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "integration_id" => $this->integration_id,
            "enabled" => true,
            "token" => User::v4()
        ]);

        // admin is never granted at signup (anyone could register the developer's email first); see reset()
        $user->admin = false;

        $user->save();
        return response()->json($user);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     operationId="User.remove",
     *     summary="Deletes the current user, or a specific user if current user is admin",
     *     tags={"User","Admin"},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User Id",
     *         required=true,
     *         @OA\Schema(type="number"),
     *      ),
     *     security={{"apiAuth":{}}},
     *     @OA\Response(response="200", description="Returns status message.")
     * )
     */
    public function remove($id) : JsonResponse {

        if(!$this->secure_mode){
            $message = ['message' => "Remove user failed, please use users endpoint."];
            return response()->json($message, 400);
        }

        $user = User::find($id);
        if ($user && $user->integration_id == $this->integration_id ) {

            // user has to be either admin or themselves
            if($user->id == $this->current_user->id || $this->is_admin) {
                $user->delete();
                // delete all their todos
                DB::table('todos')
                    ->where('integration_id', $this->integration_id)
                    ->where('user_id', $user->id)
                    ->delete();
                $message = ['message' => "User deleted ({$id})"];
                return response()->json($message);
            }
            else{
                $message = ['message' => "Not authorized to delete user ({$id})"];
                return response()->json($message, 403);
            }
        }
        else {
            $message = ['message' => "User not found ({$id})"];
            return response()->json($message, 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users",
     *     operationId="User.removeAll",
     *     summary="Deletes all users, current user must have admin rights.",
     *     tags={"Admin"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Parameter(
     *         name="admin",
     *         in="query",
     *         description="?admin=true if you want to also delete the admin account (not developer account)",
     *         required=false,
     *         @OA\Schema(type="boolean"),
     *      ),
     *     @OA\Response(response="200", description="Returns status message.")
     * )
     */
    public function removeAll(Request $request): JsonResponse {

        if(!$this->secure_mode){
            $message = ['message' => "Remove all users failed, please use users endpoint."];
            return response()->json($message, 400);
        }

        $force = false;
        // checking to see if admin was passed in and what's the value
        if($request->has('admin')) {
            $force = $request->get('admin') == "true";
        }

        // this will delete all users and all todos belonging to users
        if ($this->is_admin){
            $users_query = DB::table('users')->where('integration_id', $this->integration_id);
            // if no admin passed in, preserve admin
            if(!$force) {
                $users_query->where("admin", "!=", true);
            }
            $users_query->delete();
            $todos_query = DB::table('todos')->where('integration_id', $this->integration_id);
            // if no admin passed in, preserve admin
            if(!$force) {
                $todos_query->where("user_id", "!=", $this->current_user->id);
            }
            $todos_query->whereNotNull('user_id')->delete();
            return response()->json(["message"=>"All" . (!$force ? " other " : " ") . "users and their todos have been deleted." .
                ($force ? "You have been logged out." : " Your account and todos still exist.") ]);
        }
        else{
            $message = ['message' => "Delete all users failed. Only admins can delete all users"];
            return response()->json($message, 403);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     operationId="User.index",
     *     summary="Lists all users, if the current user has admin rights.",
     *     tags={"Admin"},
     *     security={{"apiAuth":{}}},
     *     @OA\Parameter(
     *         name="apikey",
     *         in="query",
     *         description="all api calls require the ?apikey querystring.",
     *         required=true,
     *         @OA\Schema(type="string"),
     *      ),
     *     @OA\Response(response="200", description="Returns list of all users.")
     * )
     */
    public function index(): JsonResponse {
        if(!$this->secure_mode){
            $message = ['message' => "List all users failed, please use users endpoint."];
            return response()->json($message, 400);
        }

        if(!$this->is_admin){
            $message = ['message' => "List all users failed.  Only admins can list all users."];
            return response()->json($message, 403);
        }

        $query = User::where("integration_id", $this->integration_id)->get();

        return response()->json($query);
    }

    private const RESET_MINUTES = 15;
    private const RESET_MAX_ATTEMPTS = 5;
    private const RESET_COOLDOWN_SECONDS = 60;
    private const RESET_DAILY_LIMIT = 20;

    /**
     * @OA\Post(
     * path="/api/users/forgot",
     * summary="Email a password reset code",
     * description="Always answers with the same message, whether or not the email has an account. The code is 6 digits, valid for 15 minutes and works once. One request per minute per account, and 20 per day per developer key.",
     * operationId="UserController.Forgot",
     * tags={"Users"},
     * @OA\Parameter(name="apikey", in="query", required=true, @OA\Schema(type="string")),
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Request accepted",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="If an account exists for that email, a reset code has been sent.")
     *    )
     * ),
     * @OA\Response(response=429, description="Daily reset limit for this developer key reached")
     * )
     */
    public function forgot(Request $request) : JsonResponse{

        $this->validate($request, [
            'email' => 'required|email|max:255'
        ]);

        $generic = ['message' => "If an account exists for that email, a reset code has been sent."];

        // limit how many reset emails one developer key can trigger per day (emails are not verified at signup)
        $sent_today = User::where("integration_id", $this->integration_id)
            ->where("reset_sent_at", ">", Carbon::now()->subDay())->count();
        if($sent_today >= self::RESET_DAILY_LIMIT){
            return response()->json(['message' => "Password reset limit reached for this developer account today, please try again tomorrow."], 429);
        }

        $user = User::where([["email", $request->email], ["integration_id", $this->integration_id]])->first();

        if($user && $user->enabled
            && (!$user->reset_sent_at || $user->reset_sent_at->diffInSeconds(Carbon::now()) >= self::RESET_COOLDOWN_SECONDS)){

            $code = str_pad((string)random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $user->reset_code_hash = Hash::make($code);
            $user->reset_expires_at = Carbon::now()->addMinutes(self::RESET_MINUTES);
            $user->reset_attempts = 0;
            $user->reset_sent_at = Carbon::now();
            $user->save();

            try {
                Mail::send('reset', [
                    "name" => $user->name,
                    "code" => $code,
                    "minutes" => self::RESET_MINUTES
                ], function ($message) use ($user) {
                    $message->to($user->email, $user->name)->subject('Your password reset code');
                });
            }
            catch (\Exception $ex) {
                // do not reveal whether the account exists or whether mail failed
                error_log("password reset mail failed: " . $ex->getMessage());
            }
        }

        return response()->json($generic);
    }

    /**
     * @OA\Post(
     * path="/api/users/reset",
     * summary="Set a new password using the emailed code",
     * description="The code works once, expires after 15 minutes and is locked after 5 wrong attempts. A successful reset signs the user out everywhere; log in again with the new password.",
     * operationId="UserController.Reset",
     * tags={"Users"},
     * @OA\Parameter(name="apikey", in="query", required=true, @OA\Schema(type="string")),
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"email","code","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="code", type="string", example="123456"),
     *       @OA\Property(property="password", type="string", format="password", example="new-password"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Password updated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Password updated, please log in.")
     *    )
     * ),
     * @OA\Response(response=400, description="Invalid or expired reset code")
     * )
     */
    public function reset(Request $request) : JsonResponse{

        $this->validate($request, [
            'email' => 'required|email|max:255',
            'code' => 'required|digits:6',
            'password' => 'required|min:8|max:72'
        ]);

        $invalid = response()->json(['message' => "Invalid or expired reset code."], 400);

        $user = User::where([["email", $request->email], ["integration_id", $this->integration_id]])->first();

        if(!$user || !$user->reset_code_hash || !$user->reset_expires_at
            || $user->reset_expires_at->isPast() || $user->reset_attempts >= self::RESET_MAX_ATTEMPTS){
            return $invalid;
        }

        if(!Hash::check($request->code, $user->reset_code_hash)){
            $user->reset_attempts = $user->reset_attempts + 1;
            $user->save();
            return $invalid;
        }

        $user->password = Hash::make($request->password);
        // the code went to the mailbox, so whoever holds it owns that address: only then is the developer's own email an admin
        if (strtolower(trim($user->email)) === strtolower(trim((string)$this->integration_email))) {
            $user->admin = true;
        }
        // sign out everywhere, and make the code single use
        $user->token = null;
        $user->reset_code_hash = null;
        $user->reset_expires_at = null;
        $user->reset_attempts = 0;
        $user->save();

        return response()->json(['message' => "Password updated, please log in."]);
    }
}
