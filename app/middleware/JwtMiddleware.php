<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class JwtMiddleware implements MiddlewareInterface
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Ignorar peticiones OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $handler->handle($request);
        }

        $uri = $request->getUri()->getPath();

        // Lista de rutas que NO requieren autenticación
        $publicRoutes = [
            '/api/auth/login', 
            '/api/health',
            '/api/password-reset/request',
            '/api/password-reset/otp',
            '/api/password-reset/verify',
            '/api/password-reset/reset'
        ];
        
        // Normalizar URI para comparación (manejar base path si es necesario)
        // Pero Slim ya nos da el path relativo si usamos getUri()->getPath() 
        // y el middleware se aplica dentro del grupo /api si lo deseamos.

        foreach ($publicRoutes as $route) {
            if (str_ends_with($uri, $route)) {
                return $handler->handle($request);
            }
        }

        $authHeader = $request->getHeaderLine('Authorization');
        $token = $this->authService->extractTokenFromHeader($authHeader);

        if (!$token) {
            return $this->errorResponse('Token no proporcionado', 401);
        }

        if (str_ends_with($uri, '/api/auth/refresh')) {
            $decoded = $this->authService->decodeTokenIgnoringExpiration($token);
        } else {
            $decoded = $this->authService->decodeToken($token);
        }

        if (!$decoded) {
            return $this->errorResponse('Token inválido o expirado', 401);
        }

        // Guardar el token decodificado en la request
        $request = $request->withAttribute('authorization', (array) $decoded);

        return $handler->handle($request);
    }

    private function errorResponse(string $message, int $status): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
