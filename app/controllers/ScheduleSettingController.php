<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ScheduleSettingServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class ScheduleSettingController
{
    use Helpers\AuthHelperTrait;

    private ScheduleSettingServiceInterface $service;
    private UserServiceInterface $userService;

    public function __construct(ScheduleSettingServiceInterface $service, UserServiceInterface $userService)
    {
        $this->service = $service;
        $this->userService = $userService;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $queryParams = $request->getQueryParams();
        $academicYearId = isset($queryParams['academic_year_id']) ? (int) $queryParams['academic_year_id'] : null;

        if (!$academicYearId) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'academic_year_id es requerido'
            ], 400);
        }

        $settings = $this->service->getSettings($teacherId, $academicYearId);
        return $this->jsonResponse($response, $settings);
    }

    public function store(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();
        $data['teacher_id'] = $teacherId;

        $validator = v::key('academic_year_id', v::intVal())
                        ->key('start_time', v::optional(v::stringType()->regex('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/')))
                        ->key('end_time', v::optional(v::stringType()->regex('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/')))
                        ->key('slot_duration', v::optional(v::intVal()->min(1)));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        $settings = $this->service->updateSettings($data);
        return $this->jsonResponse($response, [
            'status' => 'success',
            'data' => $settings
        ]);
    }
}
