<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CourseServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class CourseController
{
    use Helpers\AuthHelperTrait;

    private CourseServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(CourseServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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

        $courses = $this->service->getAllCoursesByTeacher($teacherId, $deleted);
        return $this->jsonResponse($response, $courses);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $course = $this->service->getCourseById($id, $teacherId);

        if (!$course) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Course not found'], 404);
        }

        return $this->jsonResponse($response, $course);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;
        $data['status'] = $data['status'] ?? 1;

        $validator = v::key('academic_year_id', v::intVal())
                        ->key('name', v::stringType()->length(1, 50))
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

        $course = $this->service->createCourse($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $course], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('academic_year_id', v::optional(v::intVal()))
                        ->key('name', v::optional(v::stringType()->length(1, 50)))
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

        $course = $this->service->updateCourse($id, $teacherId, $data);

        if (!$course) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Course not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $course]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteCourse($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Course not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Course deleted']);
    }
}
