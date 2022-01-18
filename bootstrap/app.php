<?php

use App\Providers\CatchAllOptionsRequestsProvider;
use LumenRateLimiting\ThrottleRequests;

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$integration_id = '';

$app->withFacades();

$app->withEloquent();

if(config('app.env')!= 'production') {
    $app->configure( 'swagger-lume');
    $app->register(\SwaggerLume\ServiceProvider::class);
}


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

 $app->middleware([
     App\Http\Middleware\CorsMiddleware::class
 ]);

$app->routeMiddleware([
    'throttle' => ThrottleRequests::class,
]);

$app->register(App\Providers\AppServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);

$app->configure('services');
$app->configure('mail');

// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);


$app->router->group([
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'throttle:global',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
