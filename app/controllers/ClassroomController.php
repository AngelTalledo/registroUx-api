<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ClassroomServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class ClassroomController
{
    use Helpers\AuthHelperTrait;

    private ClassroomServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(ClassroomServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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

        $classrooms = $this->service->getAllClassroomsByTeacher($teacherId, $deleted);
        return $this->jsonResponse($response, $classrooms);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $classroom = $this->service->getClassroomById($id, $teacherId);

        if (!$classroom) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Classroom not found'], 404);
        }

        return $this->jsonResponse($response, $classroom);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['status'] = $data['status'] ?? 1;
        $data['teacher_id'] = $teacherId;

        $validator = v::key('academic_year_id', v::intVal())
                        ->key('course_id', v::intVal())
                        ->key('grade_id', v::intVal())
                        ->key('section', v::stringType()->length(1, 10))
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

        // Ownership of course/grade/period should be checked in Service if needed, 
        // but here we just pass the data.
        $classroom = $this->service->createClassroom($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $classroom], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('academic_year_id', v::optional(v::intVal()))
                        ->key('course_id', v::optional(v::intVal()))
                        ->key('grade_id', v::optional(v::intVal()))
                        ->key('section', v::optional(v::stringType()->length(1, 10)))
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

        $classroom = $this->service->updateClassroom($id, $teacherId, $data);

        if (!$classroom) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Classroom not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $classroom]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteClassroom($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Classroom not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Classroom deleted']);
    }
}
