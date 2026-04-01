<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Throwable;

class ErrorHandlerMiddleware
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function __invoke(Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): Response
    {
        $statusCode = 500;
        if (is_int($exception->getCode()) && $exception->getCode() >= 400 && $exception->getCode() < 600) {
            $statusCode = $exception->getCode();
        }

        $payload = [
            'status' => 'error',
            'message' => $exception->getMessage(),
        ];

        if ($displayErrorDetails) {
            $payload['details'] = [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        $response = $this->app->getResponseFactory()->createResponse();
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
