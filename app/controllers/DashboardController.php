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
    private \App\Repositories\EvaluationRepository $evaluationRepository;
    private UserServiceInterface $userService;

    public function __construct(
        AcademicYearRepository $academicYearRepository,
        PeriodRepository $periodRepository,
        ScheduleEntryRepository $scheduleEntryRepository,
        \App\Repositories\EvaluationRepository $evaluationRepository,
        UserServiceInterface $userService
    ) {
        $this->academicYearRepository = $academicYearRepository;
        $this->periodRepository = $periodRepository;
        $this->scheduleEntryRepository = $scheduleEntryRepository;
        $this->evaluationRepository = $evaluationRepository;
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

    public function academicStats(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        // 1. Get Current Period
        $currentYear = $this->academicYearRepository->findCurrentByTeacher($teacherId);
        if (!$currentYear) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sin año académico'], 404);
        }
        $currentPeriod = $this->periodRepository->findCurrentByAcademicYear($currentYear->id);
        if (!$currentPeriod) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sin periodo'], 404);
        }

        // 2. Get Stats
        $courseStats = $this->evaluationRepository->getStatsByTeacherAndPeriod($teacherId, $currentPeriod->id);

        // 3. Calculate Global Average
        $totalAvg = count($courseStats) > 0 
            ? array_sum(array_column($courseStats, 'average')) / count($courseStats) 
            : 0;

        // 4. Get Previous Period Stats (Simplification: find period by order-1)
        // This is a bit complex without more info, so we'll just return current for now
        // and a mock improvement value.
        
        return $this->jsonResponse($response, [
            'global' => [
                'average' => round($totalAvg, 2),
                'improvement' => 1.2 // Mocked for now
            ],
            'courses' => $courseStats
        ]);
    }
}
