<?php

// use Psr\Container\ContainerInterface;

// return function (ContainerInterface $container) {
//     // Load environment variables
//     $envLoader = require __DIR__ . '/../config/env.php';
//     $envLoader();

//     // Add database connection to the container
//     $container->set('db', require __DIR__ . '/../config/database.php');
// };

use Psr\Container\ContainerInterface;
use App\Controllers\UserController;
//use App\Models\dbHelper

// return function (ContainerInterface $container) {
//     // Load environment variables
//     $envLoader = require __DIR__ . '/env.php';
//     $envLoader();

//     // Register the database connection
//     $container->set('db', require __DIR__ . '/database.php');

//     // Register UserController with its dependency
//     $container->set(UserController::class, function (ContainerInterface $container) {
//         return new UserController($container->get('db'));
//     });
// };

return function (ContainerInterface $container) {
    // Load environment variables
    $envLoader = require __DIR__ . '/env.php';
    $envLoader();

    // Register the database connection
    //$container->set('db', require __DIR__ . '/database.php');
    //$container->set('dbHelper', require __DIR__ . '/dbHelper.php');

    // Register UserController with its dependency
    // $container->set(UserController::class, function (ContainerInterface $container) {
    //     //return new UserController($container->get('db'));
    //     return new UserController($container->get('dbHelper'));
    // });
    // Register dbHelper
    $container->set(dbHelper::class, function ($container) {
        $settings = $container->get('settings')['db'];
        return new dbHelper($settings);
    });


};

