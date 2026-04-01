<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\EvaluationServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class EvaluationController
{
    use Helpers\AuthHelperTrait;

    private EvaluationServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(EvaluationServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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
            'session_competency_id' => isset($queryParams['session_competency_id']) ? (int) $queryParams['session_competency_id'] : null,
            'student_id' => isset($queryParams['student_id']) ? (int) $queryParams['student_id'] : null,
        ];

        $evaluations = $this->service->getAllEvaluationsByTeacher($teacherId, $filters);
        return $this->jsonResponse($response, $evaluations);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $evaluation = $this->service->getEvaluationById($id, $teacherId);

        if (!$evaluation) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Evaluación no encontrada'], 404);
        }

        return $this->jsonResponse($response, $evaluation);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;

        $validator = v::key('session_competency_id', v::intVal())
                        ->key('student_id', v::intVal())
                        ->key('grade', v::stringType()->notEmpty());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $evaluation = $this->service->createEvaluation($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $evaluation], 201);
    }

    public function upsert(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;

        $validator = v::key('session_competency_id', v::intVal())
                        ->key('student_id', v::intVal())
                        ->key('grade', v::stringType()->notEmpty());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $evaluation = $this->service->upsertEvaluation($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $evaluation]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('grade', v::optional(v::stringType()->notEmpty()));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $evaluation = $this->service->updateEvaluation($id, $teacherId, $data);

        if (!$evaluation) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Evaluación no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $evaluation]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteEvaluation($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Evaluación no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Evaluación eliminada']);
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

        $report = $this->service->getEvaluationReport(
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

    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
