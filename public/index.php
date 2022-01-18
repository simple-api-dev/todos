<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

ini_set("display_errors", 1);
ini_set("error_log", "./my-errors.log");

$app = require __DIR__.'/../bootstrap/app.php';

$app->run();
