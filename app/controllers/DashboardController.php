<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AcademicYearRepository;
use App\Repositories\PeriodRepository;
use App\Repositories\ScheduleEntryRepository;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    use Helpers\AuthHelperTrait;

    private AcademicYearRepository $academicYearRepository;
    private PeriodRepository $periodRepository;
    private ScheduleEntryRepository $scheduleEntryRepository;
    private UserServiceInterface $userService;

    public function __construct(
        AcademicYearRepository $academicYearRepository,
        PeriodRepository $periodRepository,
        ScheduleEntryRepository $scheduleEntryRepository,
        UserServiceInterface $userService
    ) {
        $this->academicYearRepository = $academicYearRepository;
        $this->periodRepository = $periodRepository;
        $this->scheduleEntryRepository = $scheduleEntryRepository;
        $this->userService = $userService;
    }

    public function currentSchedule(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $queryParams = $request->getQueryParams();
        $dayOfWeek = isset($queryParams['day_of_week']) ? (int) $queryParams['day_of_week'] : (int) date('N');

        // 1. Find Current Academic Year
        $currentYear = $this->academicYearRepository->findCurrentByTeacher($teacherId);
        
        if (!$currentYear) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'No hay un año académico marcado como actual'
            ], 404);
        }

        // 2. Find Current Period in that Year
        $currentPeriod = $this->periodRepository->findCurrentByAcademicYear($currentYear->id);

        if (!$currentPeriod) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'No hay un periodo marcado como actual para este año'
            ], 404);
        }

        // 3. Find Schedule for that Period and Day
        $schedule = $this->scheduleEntryRepository->findByTeacherPeriodAndDay($teacherId, $currentPeriod->id, $dayOfWeek);

        return $this->jsonResponse($response, [
            [
                'yaer' => $currentYear->year,
                'periodo' => $currentPeriod->name,
                'shedule' => $schedule
            ]
        ]);
    }
}
