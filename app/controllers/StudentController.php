<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StudentServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class StudentController
{
    use Helpers\AuthHelperTrait;

    private StudentServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(StudentServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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
        $filters = [
            'classroom_id' => isset($queryParams['classroom_id']) ? (int) $queryParams['classroom_id'] : null,
            'course_id' => isset($queryParams['course_id']) ? (int) $queryParams['course_id'] : null,
            'grade_id' => isset($queryParams['grade_id']) ? (int) $queryParams['grade_id'] : null,
            'deleted' => isset($queryParams['deleted']) && $queryParams['deleted'] === 'true',
        ];

        $students = $this->service->getAllStudentsByTeacher($teacherId, $filters);
        return $this->jsonResponse($response, $students);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $student = $this->service->getStudentById($id, $teacherId);

        if (!$student) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Estudiante no encontrado'], 404);
        }

        return $this->jsonResponse($response, $student);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;

        $validator = v::key('dni', v::stringType()->length(8, 20))
                        ->key('names', v::stringType()->length(1, 100))
                        ->key('last_names', v::stringType()->length(1, 100))
                        ->key('classroom_id', v::intVal())
                        ->key('course_id', v::intVal())
                        ->key('grade_id', v::intVal())
                        ->key('gender', v::optional(v::stringType()->length(1, 20)))
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

        $student = $this->service->createStudent($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $student], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('dni', v::optional(v::stringType()->length(8, 20)))
                        ->key('names', v::optional(v::stringType()->length(1, 100)))
                        ->key('last_names', v::optional(v::stringType()->length(1, 100)))
                        ->key('classroom_id', v::optional(v::intVal()))
                        ->key('course_id', v::optional(v::intVal()))
                        ->key('grade_id', v::optional(v::intVal()))
                        ->key('gender', v::optional(v::stringType()->length(1, 20)))
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

        $student = $this->service->updateStudent($id, $teacherId, $data);

        if (!$student) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Estudiante no encontrado'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $student]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteStudent($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Estudiante no encontrado'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Estudiante eliminado']);
    }

    public function myCourses(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $courses = $this->service->getMyCourses($teacherId);
        return $this->jsonResponse($response, $courses);
    }
}
