<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserServiceInterface;
use App\Services\AuthServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class AuthController
{
    use Helpers\AuthHelperTrait;

    private UserServiceInterface $userService;
    private LoggerInterface $logger;
    private AuthServiceInterface $authService;

    public function __construct(UserServiceInterface $userService, LoggerInterface $logger, AuthServiceInterface $authService)
    {
        $this->userService = $userService;
        $this->logger = $logger;
        $this->authService = $authService;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->logger->info("Intento de login", ['email' => $data['email'] ?? 'desconocido']);

        $validator = v::key('email', v::email())
                        ->key('password', v::stringType()->notEmpty());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'errors' => $e->getMessages()], 400);
        }

        $user = $this->userService->authenticateUser($data['email'], $data['password']);

        if (!$user) {
            $this->logger->warning("Login fallido: Credenciales incorrectas", ['email' => $data['email']]);
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Credenciales inválidas'], 401);
        }

        $issuedAt = time();
        $expirationTime = $issuedAt + (60 * 60 * 24 * 7); // Token válido por 7 días
        $teacherId = $user->teacher ? $user->teacher->id : null;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $user->id,
            'email' => $user->email,
            'teacher_id' => $teacherId
        ];

        $token = $this->authService->generateToken($payload);
        $this->logger->info("Login exitoso", ['user_id' => $user->id]);

        return $this->jsonResponse($response, [
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'teacher_id' => $user->teacher ? $user->teacher->id : null,
                    'teacher' => $user->teacher
                ]
            ]
        ]);
    }

    public function session(Request $request, Response $response): Response
    {
        $user = $this->getAuthenticatedUser($request, $this->userService);

        if (!$user) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sesión no válida o usuario no encontrado'], 401);
        }

        return $this->jsonResponse($response, [
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'teacher_id' => $user->teacher ? $user->teacher->id : null,
                    'teacher' => $user->teacher
                ]
            ]
        ]);
    }

    public function refresh(Request $request, Response $response): Response
    {
        $user = $this->getAuthenticatedUser($request, $this->userService);

        if (!$user) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'No se puede renovar el token: sesión inválida'], 401);
        }

        $issuedAt = time();
        $expirationTime = $issuedAt + (60 * 60 * 24 * 7); // Otros 7 días
        $teacherId = $user->teacher ? $user->teacher->id : null;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $user->id,
            'email' => $user->email,
            'teacher_id' => $teacherId
        ];

        $token = $this->authService->generateToken($payload);

        return $this->jsonResponse($response, [
            'status' => 'success',
            'data' => [
                'token' => $token
            ]
        ]);
    }
}
