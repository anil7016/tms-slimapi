<?php

namespace App\Utils;

use Psr\Http\Message\ResponseInterface as Response;

class JsonResponse
{
    public static function respond(Response $response, $data, int $status = 200, string $message = 'OK'): Response
    {
        $payload = json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], JSON_PRETTY_PRINT);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
