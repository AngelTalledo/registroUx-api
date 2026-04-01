<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SessionServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class SessionController
{
    use Helpers\AuthHelperTrait;

    private SessionServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(SessionServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $sessions = $this->service->getAllSessionsByTeacher($teacherId);
        return $this->jsonResponse($response, $sessions);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $session = $this->service->getSessionById($id, $teacherId);

        if (!$session) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sesión no encontrada'], 404);
        }

        return $this->jsonResponse($response, $session);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;

        $validator = v::key('period_id', v::intVal())
                        ->key('course_id', v::intVal())
                        ->key('grade_id', v::intVal())
                        ->key('classroom_id', v::intVal())
                        ->key('date', v::date())
                        ->key('theme', v::stringType()->length(1, 255))
                        ->key('type', v::stringType()->length(1, 50));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $session = $this->service->createSession($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $session], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('period_id', v::optional(v::intVal()))
                        ->key('course_id', v::optional(v::intVal()))
                        ->key('grade_id', v::optional(v::intVal()))
                        ->key('classroom_id', v::optional(v::intVal()))
                        ->key('date', v::optional(v::date()))
                        ->key('theme', v::optional(v::stringType()->length(1, 255)))
                        ->key('type', v::optional(v::stringType()->length(1, 50)));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $session = $this->service->updateSession($id, $teacherId, $data);

        if (!$session) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sesión no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $session]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteSession($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sesión no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Sesión eliminada']);
    }

    public function deleted(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $queryParams = $request->getQueryParams();
        $filters = [
            'course_id' => isset($queryParams['course_id']) ? (int) $queryParams['course_id'] : null,
            'classroom_id' => isset($queryParams['classroom_id']) ? (int) $queryParams['classroom_id'] : null,
            'grade_id' => isset($queryParams['grade_id']) ? (int) $queryParams['grade_id'] : null,
            'period_id' => isset($queryParams['period_id']) ? (int) $queryParams['period_id'] : null,
        ];

        $sessions = $this->service->getDeletedSessionsByTeacher($teacherId, $filters);
        return $this->jsonResponse($response, $sessions);
    }

    public function restore(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $session = $this->service->restoreSession($id, $teacherId);

        if (!$session) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sesión no encontrada o no eliminada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $session]);
    }
}
