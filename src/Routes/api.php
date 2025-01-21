<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;
//use App\Middleware\AuthMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\UserController;

$secretKey = 'TMS'; // Define secret key outside the route group

return function (App $app) use ($secretKey) { // Pass $secretKey into the closure using `use`

    // Public route
    $app->get('/', function (Request $request, Response $response, array $args) {
        $response->getBody()->write("Hello world!");
        return $response;
    });

    // Test route
    $app->get('/test', function (Request $request, Response $response, array $args) {
        $payload = json_encode(['hello' => 'world'], JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Grouping API routes with authentication middleware
    $app->group('/api/v1', function ($group) use ($secretKey) { // Use `use($secretKey)` to pass the key to this scope

        // Authentication route
        $group->post('/authenticate', [UserController::class, 'authenticate']);
        // User routes with AuthMiddleware applied
        $group->get('/userList', UserController::class . ':getUsers')->add(new AuthMiddleware($secretKey)); // Passing secretKey to middleware
        // Specific user details route
        $group->get('/users/{id}', UserController::class . ':getUserDataById')->add(new AuthMiddleware($secretKey)); // Passing secretKey to middleware
        // Profile route
        $group->get('/getProfile/{id}', UserController::class . ':getUserByField')->add(new AuthMiddleware($secretKey));

        $group->post('/checkusername', [UserController::class, 'checkusername'])->add(new AuthMiddleware($secretKey));
        $group->get('/users', UserController::class . ':userlist')->add(new AuthMiddleware($secretKey));
        $group->post('/checkusernameExist', [UserController::class, 'checkusernameExist'])->add(new AuthMiddleware($secretKey));
        $group->post('/checkemailaddress', [UserController::class, 'checkemailaddress'])->add(new AuthMiddleware($secretKey));

    }); // End of API routes group

};