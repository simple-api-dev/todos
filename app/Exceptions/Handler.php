<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Throwable $exception
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            $errors = [];
            foreach($exception->errors() as $error){
                $errors[] = $error[0];
            }
            return response()->json([
                'message' => implode("  ", $errors)
            ], 422);
        }

        if ($this->isHttpException($exception)) {
            if ($exception->getStatusCode() == 404) {
                return response()->json( ["message"=>"Not a valid api route - not found."], 404);
            }
            if($exception->getStatusCode() == 405) {
                return response()->json( ["message"=>$request->getMethod() . " is not configured for this route.[343]"], 405);
            }
            if($exception->getStatusCode() == 500) {
                return response()->json( ["message"=>"Server error.  Please file a bug."], 500);
            }
            if($exception->getStatusCode() == 429) {
                return response()->json( ["message"=>"Throttle Limit Exceeded."], 429);
            }
            return response()->json(["message"=>$exception->getMessage()],$exception->getStatusCode());
         }

        return parent::render($request, $exception);
    }
}
