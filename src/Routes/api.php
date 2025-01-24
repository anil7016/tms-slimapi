<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy;
use App\Middleware\AuthMiddleware;

use App\Controllers\UserController;

$secretKey = 'TMS'; 

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

        $group->post('/resetpassword1', [UserController::class, 'resetpassword1'])->add(new AuthMiddleware($secretKey));
        $group->post('/changepassword', [UserController::class, 'changepassword'])->add(new AuthMiddleware($secretKey));
        $group->put('/userUpdate_Byid/{id}', [UserController::class, 'userUpdate_Byid'])->add(new AuthMiddleware($secretKey));
        $group->put('/updateUserTabsortorder/{id}', [UserController::class, 'updateUserTabsortorder'])->add(new AuthMiddleware($secretKey));
        $group->post('/saveuserprofileinternal', [UserController::class, 'saveuserprofile'])->add(new AuthMiddleware($secretKey));
        $group->put('/saveuserprofileinternal/{id}', [UserController::class, 'saveuserprofileinternalupdate'])->add(new AuthMiddleware($secretKey));
        $group->post('/saveuserprofileexternelS', [UserController::class, 'saveuserprofileexternelS'])->add(new AuthMiddleware($secretKey));
        $group->post('/saveuserProfileSignUp', [UserController::class, 'saveuserProfileSignUp'])->add(new AuthMiddleware($secretKey));
        
        $group->put('/saveuserprofileexternel/{id}', [UserController::class, 'saveuserprofileexternelupdate'])->add(new AuthMiddleware($secretKey));
        $group->get('/getProfile/{id}', UserController::class . ':getUserByField')->add(new AuthMiddleware($secretKey));
        $group->put('/saveuserprofile/{id}', [UserController::class, 'saveuserprofile'])->add(new AuthMiddleware($secretKey));
        //$group->post('/ContactAdd', [UserController::class, 'ContactAdd'])->add(new AuthMiddleware($secretKey));
        $group->get('/userProfileNumber/{id}', UserController::class . ':userProfileNumberGet')->add(new AuthMiddleware($secretKey));
        $group->get('/getAlluserGroup', UserController::class . ':getAlluserGroup')->add(new AuthMiddleware($secretKey)); // Passing secretKey to middleware
        $group->get('/getTreeMenu', UserController::class . ':getTreeMenu')->add(new AuthMiddleware($secretKey)); // Passing secretKey to middleware
        $group->put('/updateAbscentDate/{id}', [UserController::class, 'updateAbscentDate'])->add(new AuthMiddleware($secretKey));
        $group->get('/getUserDataById/{id}', UserController::class . ':getUserDataById')->add(new AuthMiddleware($secretKey));
        $group->post('/checkusername', [UserController::class, 'checkusername'])->add(new AuthMiddleware($secretKey));
        $group->post('/checkusernameExist', [UserController::class, 'checkusernameExist'])->add(new AuthMiddleware($secretKey));
        $group->post('/checkemailaddress', [UserController::class, 'checkemailaddress'])->add(new AuthMiddleware($secretKey));
        $group->get('/users', UserController::class . ':userlist')->add(new AuthMiddleware($secretKey));
        
        $group->get('/user/{type}', UserController::class . ':userwithType')->add(new AuthMiddleware($secretKey));
        $group->get('/userQaSpecialist/{type}', UserController::class . ':userQaSpecialist')->add(new AuthMiddleware($secretKey));
        $group->get('/userManager/{type}', UserController::class . ':userManager')->add(new AuthMiddleware($secretKey));
        $group->get('/userCoordinator/{type}', UserController::class . ':userCoordinator')->add(new AuthMiddleware($secretKey));
        
        $group->delete('/deleteUser/{id}/{image}', UserController::class . ':deleteUser')->add(new AuthMiddleware($secretKey));
        $group->get('/userGetmessage/{id}', UserController::class . ':userlist')->add(new AuthMiddleware($secretKey));
        $group->get('/clientlistindirectGet/{id}', UserController::class . ':clientlistindirectGet')->add(new AuthMiddleware($secretKey));
        
        $group->get('/userExternalGet/{id}', UserController::class . ':userExternalGet')->add(new AuthMiddleware($secretKey));
        $group->get('/messageUserOneget/{id}', UserController::class . ':messageUserOneget')->add(new AuthMiddleware($secretKey));
        $group->get('/viewExternalget/{id}', UserController::class . ':viewExternalget')->add(new AuthMiddleware($secretKey));
        $group->get('/cityTimeZoneget/{id}', UserController::class . ':cityTimeZoneget')->add(new AuthMiddleware($secretKey));
        $group->post('/getTimeZoneByLatLong', UserController::class . ':getTimeZoneByLatLong')->add(new AuthMiddleware($secretKey));
        
        // Add functions class
        $group->post('/sendAcountActivationlink', [UserController::class, 'sendAcountActivationlink'])->add(new AuthMiddleware($secretKey));
        
        $group->get('/getAllsentEmail', UserController::class . 'getAllsentEmail')->add(new AuthMiddleware($secretKey));
        $group->post('/getMultipleReourses', [UserController::class, 'getMultipleReourse'])->add(new AuthMiddleware($secretKey));

        
        $group->get('/userList2', UserController::class . ':getUsers')->add(new AuthMiddleware($secretKey)); // Passing secretKey to middleware
        $group->get('/users/{id}', UserController::class . ':getSingleUserById')->add(new AuthMiddleware($secretKey)); // Passing secretKey to middleware
        // Profile route


    }); // End of API routes group

};