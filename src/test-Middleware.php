<?php

use Slim\App;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    $app->add(AuthMiddleware::class); // Ensure the correct class is registered
};
