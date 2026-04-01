<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ScheduleEntryServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class ScheduleEntryController
{
    use Helpers\AuthHelperTrait;

    private ScheduleEntryServiceInterface $service;
    private UserServiceInterface $userService;

    public function __construct(ScheduleEntryServiceInterface $service, UserServiceInterface $userService)
    {
        $this->service = $service;
        $this->userService = $userService;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $queryParams = $request->getQueryParams();
        $academicPeriodId = isset($queryParams['academic_period_id']) ? (int) $queryParams['academic_period_id'] : null;
        $dayOfWeek = isset($queryParams['day_of_week']) ? (int) $queryParams['day_of_week'] : null;

        if (!$academicPeriodId) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'academic_period_id es requerido'
            ], 400);
        }

        if ($dayOfWeek) {
            $schedule = $this->service->getScheduleByDay($teacherId, $academicPeriodId, $dayOfWeek);
        } else {
            $schedule = $this->service->getSchedule($teacherId, $academicPeriodId);
        }

        return $this->jsonResponse($response, $schedule);
    }

    public function store(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();
        
        $validator = v::key('academic_period_id', v::intVal())
                        ->key('entries', v::arrayType()->notEmpty());

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        // Validate individual entries (basic validation)
        foreach ($data['entries'] as $entry) {
            if (!isset($entry['day_of_week'], $entry['start_time'], $entry['end_time'])) {
                return $this->jsonResponse($response, [
                    'status' => 'error',
                    'message' => 'Cada entrada debe tener day_of_week, start_time y end_time'
                ], 400);
            }
        }

        $this->service->saveSchedule($teacherId, (int)$data['academic_period_id'], $data['entries']);
        
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => 'Horario guardado correctamente'
        ]);
    }
}
