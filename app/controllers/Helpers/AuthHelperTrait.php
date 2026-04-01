<?php

declare(strict_types=1);

namespace App\Controllers\Helpers;

use App\Services\UserServiceInterface;
use App\Models\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

trait AuthHelperTrait
{
    /**
     * Gets the authenticated user and their teacher profile from the request token.
     */
    protected function getAuthenticatedUser(Request $request, UserServiceInterface $userService): ?User
    {
        $token = $request->getAttribute('authorization');
        
        if (!$token) {
            return null;
        }

        $userId = is_array($token) ? ($token['sub'] ?? null) : ($token->sub ?? null);
        
        if (!$userId) {
            return null;
        }

        return $userService->getUserWithTeacher((int) $userId);
    }

    /**
     * Resolves the teacher_id from the session or returns an error response.
     * 
     * @return int|Response
     */
    protected function resolveTeacherIdOrResponse(Request $request, Response $response, UserServiceInterface $userService)
    {
        $user = $this->getAuthenticatedUser($request, $userService);
        
        if (!$user || !$user->teacher) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Sesión no válida o usuario no encontrado'
            ], 401);
        }

        return (int) $user->teacher->id;
    }

    /**
     * Helper to return a JSON response.
     */
    protected function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * Gets the full base URL including subdirectories.
     */
    protected function getBaseUrl(Request $request): string
    {
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $port = $uri->getPort();
        
        $base = $scheme . '://' . $host . ($port ? ':' . $port : '');
        
        // Match the base path logic in index.php
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('\\', '/', dirname($scriptName));
        $basePath = $basePath === '/' ? '' : $basePath;
        
        return rtrim($base, '/') . '/' . ltrim($basePath, '/');
    }
}
