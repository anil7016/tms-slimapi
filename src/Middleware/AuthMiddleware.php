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

// namespace App\Middleware;

// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
// use Psr\Http\Server\MiddlewareInterface;

// class AuthMiddleware implements MiddlewareInterface
// {
//     public function process(Request $request, RequestHandler $handler): Response
//     {
//         // Extract Authorization header
//         $headers = $request->getHeader('Authorization');
//         $token = $headers[0] ?? null;

//         if (!$token || $token !== '0e7517141fb53f21ee439b355b5a1d0a') {
//             $response = new \Slim\Psr7\Response();
//             $response->getBody()->write(json_encode([
//                 'status' => 401,
//                 'message' => 'Unauthorized'
//             ], JSON_PRETTY_PRINT));
//             return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
//         }

//         // Proceed to the next middleware or request handler
//         return $handler->handle($request);
//     }
// }


namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    private $secretKey;

    // Constructor to accept the secret key
    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 401,
                'message' => 'Authentication token is missing or invalid.'
            ], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $request = $request->withAttribute('user', (array)$decoded->data);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 401,
                'message' => 'Invalid or expired token.'
            ], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        return $handler->handle($request);
    }
}


