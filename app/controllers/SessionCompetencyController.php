<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SessionCompetencyServiceInterface;
use App\Services\SessionServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class SessionCompetencyController
{
    use Helpers\AuthHelperTrait;

    private SessionCompetencyServiceInterface $service;
    private SessionServiceInterface $sessionService;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(
        SessionCompetencyServiceInterface $service, 
        SessionServiceInterface $sessionService,
        UserServiceInterface $userService, 
        LoggerInterface $logger
    ) {
        $this->service = $service;
        $this->sessionService = $sessionService;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $queryParams = $request->getQueryParams();
        $filters = [
            'course_id' => isset($queryParams['course_id']) ? (int) $queryParams['course_id'] : null,
        ];

        $records = $this->service->getAllSessionCompetenciesByTeacher($teacherId, $filters);
        return $this->jsonResponse($response, $records);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        // Map Spanish field names to English
        if (isset($data['curso_id'])) $data['course_id'] = $data['curso_id'];
        if (isset($data['grado_id'])) $data['grade_id'] = $data['grado_id'];
        if (isset($data['aula_id'])) $data['classroom_id'] = $data['aula_id'];

        $validator = v::key('competency_id', v::intVal())
                        ->key('period_id', v::intVal())
                        ->key('course_id', v::intVal())
                        ->key('grade_id', v::intVal())
                        ->key('classroom_id', v::intVal())
                        ->key('date', v::date())
                        ->key('theme', v::optional(v::stringType()))
                        ->key('type', v::optional(v::stringType()));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $data['teacher_id'] = $teacherId;

        $record = $this->service->createSessionCompetency($data);
        return $this->jsonResponse($response, ['status' => 'success', 'data' => $record], 201);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deleteSessionCompetency($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Registro no encontrado'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Competencia desvinculada de la sesión']);
    }

    public function deleted(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $records = $this->service->getDeletedSessionsByTeacher($teacherId);
        return $this->jsonResponse($response, $records);
    }

    public function restore(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $restored = $this->service->handleUnifiedRestore($data, $teacherId);

        if (!$restored) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'No se pudo restablecer el registro o los datos son inválidos'], 400);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Restauración completada correctamente']);
    }

    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
