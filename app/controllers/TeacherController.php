<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\TeacherServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class TeacherController
{
    private TeacherServiceInterface $service;
    private LoggerInterface $logger;

    public function __construct(TeacherServiceInterface $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $teachers = $this->service->getAllTeachers();
        $response->getBody()->write(json_encode($teachers));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $validator = v::key('user_id', v::intVal())
                        ->key('names', v::stringType()->notEmpty())
                        ->key('last_names', v::stringType()->notEmpty())
                        ->key('gender', v::in(['F', 'M']), false); // Optional gender field, must be F or M

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'errors' => $e->getMessages()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $teacher = $this->service->createTeacher($data);
        $response->getBody()->write(json_encode(['status' => 'success', 'data' => $teacher]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('names', v::stringType()->notEmpty(), false)
                        ->key('last_names', v::stringType()->notEmpty(), false)
                        ->key('gender', v::in(['F', 'M']), false);

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'errors' => $e->getMessages()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $teacher = $this->service->updateTeacher($id, $data);

        if (!$teacher) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Teacher not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'data' => $teacher]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
