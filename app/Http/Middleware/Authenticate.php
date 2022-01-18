<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Authenticate
{

    private bool $secure_mode = false;
    private string $integration_id;

    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     * @return void
     */
    public function __construct()
    {
        $this->integration_id = app()->has('integration_id') ? app()->get('integration_id') : "";
        $this->secure_mode = app()->has('secure_mode') ? app()->get('secure_mode') : false;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure  $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $guard = null) : mixed
    {

        if ($this->secure_mode && !$request->header('Authorization')) {
            return response('Unauthorized.1', 401);
        }
        else if($this->secure_mode){
            //dd($request->header('Authorization'));
            if(str_contains($request->header('Authorization'),"Bearer ")){
                $token = explode(" ", $request->header('Authorization'))[1];

                $user = User::where([["token", $token],
                    ["integration_id", $this->integration_id]])->first();

                if($request->user_id){
                    if($user->id != $request->user_id){
                        if($user->warnings < 3) {
                            $user->warnings ++;
                            $user->save();
                            return response('hmmm, looks like someone is messing with the url string. stop that!  This is your ' .
                            $this->getWarningstring($user->warnings) .' warning!', 401);
                        }
                        else {
                            DB::table('todos')->where('user_id', $user->id)->delete();
                            return response("That's it.  You were warned.  All Todos deleted!", status: 401);
                        }
                    }
                }

                if($user){
                    // ok so user exists, has token, and is assigned to this apikey, so now assign user
                    app()->instance('current_user', $user);
                    // next let's check to see if this user is the admin of this account
                    app()->instance('is_admin', $user->admin);
                }
                else{
                    // either user toke invalid, or not part of this apikey
                    return response('Unauthorized.2', 401);
                }
            }
            else{
                return response('Unauthorized.3', 401);
            }
        }

        return $next($request);
    }

    private function getWarningString(int $warning) : string{
        return match ($warning) {
            1 => "first",
            2 => "second",
            default => "third AND FINAL",
        };
    }
}
