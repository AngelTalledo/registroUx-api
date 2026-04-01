<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AcademicYearServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class AcademicYearController
{
    use Helpers\AuthHelperTrait;

    private AcademicYearServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(AcademicYearServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $academicYears = $this->service->getAllAcademicYears();
        return $this->jsonResponse($response, $academicYears);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $academicYear = $this->service->getAcademicYearById($id, $teacherId);

        if (!$academicYear) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Academic year not found'], 404);
        }

        return $this->jsonResponse($response, $academicYear);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        
        if ($teacherId instanceof Response) {
            return $teacherId;
        }

        $data['teacher_id'] = $teacherId;

        $data['status'] = $data['status'] ?? 1;

        $validator = v::key('year', v::numericVal())
                        ->key('name', v::stringType()->length(1, 50))
                        ->key('status', v::optional(v::boolVal()))
                        ->key('is_current', v::optional(v::boolVal()));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $academicYear = $this->service->createAcademicYear($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $academicYear], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('year', v::optional(v::intVal()))
                        ->key('name', v::optional(v::stringType()->length(1, 50)))
                        ->key('status', v::optional(v::boolType()))
                        ->key('is_current', v::optional(v::boolType()));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $academicYear = $this->service->updateAcademicYear($id, $teacherId, $data);

        if (!$academicYear) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Academic year not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'data' => $academicYear]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteAcademicYear($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Academic year not found'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Academic year deleted']);
    }

    public function setCurrent(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $academicYear = $this->service->setCurrentYear($id, $teacherId);

        if (!$academicYear) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Academic year not found'], 404);
        }

        return $this->jsonResponse($response, [
            'status' => 'success', 
            'message' => 'Año académico establecido como vigente',
            'data' => $academicYear
        ]);
    }
}
