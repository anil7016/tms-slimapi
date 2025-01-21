<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;
use App\Middleware\AuthMiddleware;
use App\Controllers\UserController;

// $app->group('/users', function (RouteCollectorProxy $group) {
//     $group->get('', UserController::class . ':getUsers');
//     $group->get('/{id}', UserController::class . ':getUser');
//     $group->post('', UserController::class . ':createUser');
// });


return function (App $app) {
    //$app->addMiddleware(new \App\Middleware\AuthMiddleware());

    $app->get('/', function (Request $request, Response $response, array $args) {
        $response->getBody()->write("Hello world!");
        return $response;
    });
    $app->get('/test', function (Request $request, Response $response, array $args) {
        $payload = json_encode(['hello' => 'world'], JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    $app->group('/api/v1', function ($group) {
        $group->post('/authenticate', [UserController::class, 'authenticate']);

        $group->get('/userList', UserController::class . ':getUsers');
        $group->get('/users/{id}', UserController::class . ':getUserDataById')->add(new AuthMiddleware());
        $group->get('/getProfile/{id}', UserController::class . ':getUserByField')->add(new AuthMiddleware());
        $group->get('/getTreeMenu', UserController::class . ':getTreeMenu')->add(new AuthMiddleware());

        $group->post('/checkusername', [UserController::class, 'checkusername'])->add(new AuthMiddleware());
        $group->get('/users', UserController::class . ':userlist')->add(new AuthMiddleware());
        $group->post('/checkusernameExist', [UserController::class, 'checkusernameExist'])->add(new AuthMiddleware());
        $group->post('/checkemailaddress', [UserController::class, 'checkemailaddress'])->add(new AuthMiddleware());
        
    });

    // Protected routes with authentication
    $app->group('/secure', function ($group) {
        //$group->get('/users', UserController::class . ':getUsers');
        $group->put('/userUpdate_Byid/{id}', [UserController::class, 'userUpdateById']);
        $group->put('/updateUserTabsortorder/{id}', [UserController::class, 'updateUserTabSortOrder']);
        $group->post('/saveuserprofileinternal', [UserController::class, 'saveUserProfileInternal']);
        $group->put('/saveuserprofileinternal/{id}', [UserController::class, 'saveUserProfileInternalUpdate']);
        $group->put('/saveuserprofile/{id}', [UserController::class, 'updateUserProfile']);
        //$group->get('/getProfile/{id}', [UserController::class, 'getUserProfileById']);
        $group->post('/ContactAdd', [UserController::class, 'addContact']);
    })->add(new AuthMiddleware());

};
