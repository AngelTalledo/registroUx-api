<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DiagnosticEvaluationServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class DiagnosticEvaluationController
{
    use Helpers\AuthHelperTrait;

    private DiagnosticEvaluationServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(DiagnosticEvaluationServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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
            'period_id' => isset($queryParams['period_id']) ? (int) $queryParams['period_id'] : null,
            'student_id' => isset($queryParams['student_id']) ? (int) $queryParams['student_id'] : null,
            'course_id' => isset($queryParams['course_id']) ? (int) $queryParams['course_id'] : null,
            'aula_id' => isset($queryParams['aula_id']) ? (int) $queryParams['aula_id'] : null,
        ];

        $evaluations = $this->service->getAllEvaluationsByTeacher($teacherId, $filters);
        return $this->jsonResponse($response, $evaluations);
    }

    public function upsert(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        ($data['teacher_id'] = $teacherId);

        $validator = v::key('period_id', v::intVal())
                        ->key('student_id', v::intVal())
                        ->key('competency_id', v::intVal())
                        ->key('course_id', v::intVal())
                        ->key('aula_id', v::intVal())
                        ->key('grade', v::stringType()->length(1, 2))
                        ->key('evaluation_date', v::date('Y-m-d'));

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

    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
