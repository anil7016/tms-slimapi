<?php

use App\Models\dbHelper;
use App\Config\DatabaseConfig;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    dbHelper::class => function ($c) {
        // Create PDO instance
        $pdo = DatabaseConfig::getPDO();
        return new dbHelper($pdo);
    },
    UserController::class => function ($c) {
        return new UserController($c->get(dbHelper::class));
    }
]);

$container = $containerBuilder->build();
return $container;
