<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


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
            'password' => 'required|min:8|max:12'
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

        //check to see if email matches the dev's email in the api key integrations
        //$integration = Integration::find($this->integration_id);
        error_log($this->integration_email);
        error_log($user->email);
        if($this->integration_email == $user->email){
            $user->admin = true;
        }
        else{
            $user->admin = false;
        }

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
}
