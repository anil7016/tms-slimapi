<?php

namespace App\Utils;

use Exception;
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

    public static function respondSingle(Response $response, $returnData): Response
    {
        try {
            $payload = json_encode($returnData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            $status = isset($returnData['status']) && is_int($returnData['status']) ? $returnData['status'] : 200;

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        } catch (Exception $e) {
            // Fallback for JSON encoding errors
            $response->getBody()->write(json_encode([
                'status' => 500,
                'message' => 'Internal Server Error: Failed to encode JSON.',
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }


}
