<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CompetencyServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class CompetencyController
{
    use Helpers\AuthHelperTrait;

    private CompetencyServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(CompetencyServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
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
        $academicYearId = isset($queryParams['academic_year_id']) ? (int) $queryParams['academic_year_id'] : null;
        $deleted = isset($queryParams['deleted']) && $queryParams['deleted'] === 'true';
        
        $filters = [
            'course_id' => isset($queryParams['course_id']) ? (int) $queryParams['course_id'] : null,
            'status' => isset($queryParams['status']) ? $queryParams['status'] : null,
        ];

        $competencies = $this->service->getAllCompetenciesByTeacher($teacherId, $academicYearId, $deleted, $filters);
        return $this->jsonResponse($response, $competencies);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $competency = $this->service->getCompetencyById($id, $teacherId);

        if (!$competency) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Competencia no encontrada'], 404);
        }

        return $this->jsonResponse($response, $competency);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data['teacher_id'] = $teacherId;
        $data['status'] = $data['status'] ?? 1;

        $validator = v::key('name', v::stringType()->length(1, 100))
                        ->key('academic_year_id', v::intVal())
                        ->key('course_id', v::optional(v::intVal()))
                        ->key('description', v::optional(v::stringType()))
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

        $competency = $this->service->createCompetency($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $competency], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('name', v::optional(v::stringType()->length(1, 100)))
                        ->key('academic_year_id', v::optional(v::intVal()))
                        ->key('course_id', v::optional(v::intVal()))
                        ->key('description', v::optional(v::stringType()))
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

        $competency = $this->service->updateCompetency($id, $teacherId, $data);

        if (!$competency) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Competencia no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $competency]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteCompetency($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Competencia no encontrada'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Competencia eliminada']);
    }
}
