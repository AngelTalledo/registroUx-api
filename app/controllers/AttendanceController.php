<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AttendanceServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class AttendanceController
{
    use Helpers\AuthHelperTrait;

    private AttendanceServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(AttendanceServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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
            'session_id' => isset($queryParams['session_id']) ? (int) $queryParams['session_id'] : null,
            'student_id' => isset($queryParams['student_id']) ? (int) $queryParams['student_id'] : null,
        ];

        $attendances = $this->service->getAllAttendancesByTeacher($teacherId, $filters);
        return $this->jsonResponse($response, $attendances);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $attendance = $this->service->getAttendanceById($id, $teacherId);

        if (!$attendance) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Asistencia no encontrada'], 404);
        }

        return $this->jsonResponse($response, $attendance);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;

        $validator = v::key('session_id', v::intVal())
                        ->key('student_id', v::intVal())
                        ->key('status', v::in(['PRESENTE', 'FALTA', 'TARDANZA']));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $attendance = $this->service->createAttendance($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $attendance], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('status', v::optional(v::in(['PRESENTE', 'FALTA', 'TARDANZA'])));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $attendance = $this->service->updateAttendance($id, $teacherId, $data);

        if (!$attendance) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Asistencia no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $attendance]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteAttendance($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Asistencia no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Asistencia eliminada']);
    }

    public function report(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();

        $validator = v::key('curso_id', v::intVal())
                        ->key('aula_id', v::intVal())
                        ->key('grado_id', v::intVal());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $report = $this->service->getAttendanceReport(
            $teacherId, 
            (int)$data['curso_id'], 
            (int)$data['grado_id'], 
            (int)$data['aula_id']
        );

        return $this->jsonResponse($response, [
            'status' => 'success',
            'data' => $report
        ]);
    }

    public function saveAll(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();

        $validator = v::key('session_id', v::intVal())
                        ->key('attendances', v::arrayType()->notEmpty());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $this->service->saveBulkAttendance($teacherId, (int)$data['session_id'], $data['attendances']);

        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Asistencias registradas correctamente'
        ], 201);
    }
}
