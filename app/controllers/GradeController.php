<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\GradeServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class GradeController
{
    use Helpers\AuthHelperTrait;

    private GradeServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(GradeServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $queryParams = $request->getQueryParams();
        $deleted = isset($queryParams['deleted']) && $queryParams['deleted'] === 'true';

        $grades = $this->service->getAllGradesByTeacher($teacherId, $deleted);
        return $this->jsonResponse($response, $grades);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $grade = $this->service->getGradeById($id, $teacherId);

        if (!$grade) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Grade not found'], 404);
        }

        return $this->jsonResponse($response, $grade);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;
        $data['status'] = $data['status'] ?? 1;

        $validator = v::key('name', v::stringType()->length(1, 50))
                        ->key('status', v::optional(v::boolVal()));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $grade = $this->service->createGrade($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $grade], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('name', v::optional(v::stringType()->length(1, 50)))
                        ->key('status', v::optional(v::boolVal()));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $grade = $this->service->updateGrade($id, $teacherId, $data);

        if (!$grade) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Grade not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $grade]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteGrade($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Grade not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Grade deleted']);
    }
}
