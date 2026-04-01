<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class UserController
{
    private UserServiceInterface $service;
    private LoggerInterface $logger;

    public function __construct(UserServiceInterface $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $users = $this->service->getAllUsers();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->logger->info("Registrando usuario", ['email' => $data['email'] ?? 'desconocido']);

        $validator = v::key('email', v::email())
                        ->key('password', v::stringType()->length(6, null));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'errors' => $e->getMessages()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = $this->service->registerUser($data);
        $response->getBody()->write(json_encode(['status' => 'success', 'data' => $user]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $user = $this->service->getUserById($id);

        if (!$user) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'User not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'data' => $user]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
