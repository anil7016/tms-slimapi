<?php

//namespace App\Middleware;

// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use Psr\Http\Server\MiddlewareInterface;
// use Psr\Http\Server\RequestHandlerInterface;

// class AuthMiddleware implements MiddlewareInterface
// {
//     public function process(Request $request, RequestHandlerInterface $handler): Response
//     {
//         // Add a custom header to the response
//         $response = $handler->handle($request);
//         return $response->withHeader('X-Example-Header', 'Middleware');
//     }
// }

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Extract Authorization header
        $headers = $request->getHeader('Authorization');
        $token = $headers[0] ?? null;

        if (!$token || $token !== '0e7517141fb53f21ee439b355b5a1d0a') {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 401,
                'message' => 'Unauthorized'
            ], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Proceed to the next middleware or request handler
        return $handler->handle($request);
    }
}
