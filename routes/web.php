<?php

use App\Http\Middleware\ApiKeyAuthentication;
use App\Http\Middleware\AppendCookieMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\OriginCheckMiddleware;
use App\Http\Middleware\SecureHttpMiddleware;
use Laravel\Lumen\Routing\Router;

/** @var Router $router */


$router->group(['prefix' => env('APP_DIR'),'middleware' => [
    ApiKeyAuthentication::class,
    SecureHttpMiddleware::class]
], function () use ($router) {

    // these are the only 2 calls that would have secure true but don't require bearer_token
    $router->post('users/login', ['uses' => 'UserController@login']);
    $router->post('users/register', ['uses' => 'UserController@register']);
});


$router->group(['prefix' => env('APP_DIR'),'middleware' => [
    ApiKeyAuthentication::class,
    SecureHttpMiddleware::class,
    Authenticate::class,
    AppendCookieMiddleware::class]
    ], function () use ($router) {
        $router->get('users', ['uses' => 'UserController@index']);

        $router->delete('users', ['uses' => 'UserController@removeAll']);

        $router->delete('users/{id}', ['uses' => 'UserController@remove']);

        $router->get('todos',  ['uses' => 'TodoController@index']);

        $router->get('todos/{id}', ['uses' => 'TodoController@get']);

        $router->post('todos', ['uses' => 'TodoController@post']);

        $router->delete('todos/{id}', ['uses' => 'TodoController@remove']);

        $router->delete('todos', ['uses' => 'TodoController@removeAll']);

        $router->put('todos/{id}', ['uses' => 'TodoController@put']);

        $router->get('users/{user_id}/todos',  ['uses' => 'TodoController@index']);

        $router->get('users/{user_id}/todos/{id}', ['uses' => 'TodoController@get']);

        $router->post('users/{user_id}/todos', ['uses' => 'TodoController@post']);

        $router->delete('users/{user_id}/todos/{id}', ['uses' => 'TodoController@remove']);

        $router->delete('users/{user_id}/todos', ['uses' => 'TodoController@removeAll']);

        $router->put('users/{user_id}/todos/{id}', ['uses' => 'TodoController@put']);

        $router->post('users/logout', ['uses' => 'UserController@logout']);

});

$router->get('/', function () use ($router) {
    return redirect(getenv('APP_URL') . '/docs');
});
