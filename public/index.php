<?php

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';


// Create PHP-DI Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Load settings, dependencies, middleware, and routes
$settings = require __DIR__ . '/../src/Settings.php';
//$dependencies = require __DIR__ . '/../src/Dependencies.php';
$dependencies = require __DIR__ . '/../src/config/Dependencies.php';
//$middleware = require __DIR__ . '/../src/Middleware.php';
$routes = require __DIR__ . '/../src/Routes/api.php';

$settings($container);
$dependencies($container);
//$middleware($app);
$routes($app);

// Run the app
$app->run();
