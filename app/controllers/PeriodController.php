<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PeriodServiceInterface;
use App\Services\UserServiceInterface;
use App\Controllers\Helpers\AuthHelperTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;
use Exception;

class PeriodController
{
    use AuthHelperTrait;

    private PeriodServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(
        PeriodServiceInterface $service,
        UserServiceInterface $userService,
        LoggerInterface $logger
    ) {
        $this->service = $service;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
       
        if ($teacherId instanceof Response) return $teacherId;

        try {
            $periods = $this->service->getPeriodsForCurrentYear($teacherId);
            return $this->jsonResponse($response, $periods);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $period = $this->service->getPeriodById($id, $teacherId);

        if (!$period) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Period not found'], 404);
        }

        return $this->jsonResponse($response, $period);
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();
        $data['academic_year_id'] = (int) $data['academic_year_id'];
        $data['status'] = $data['status'] ?? 1;
        $validator = v::key('academic_year_id', v::intVal())
                        ->key('name', v::stringType()->length(1, 50))
                        ->key('start_date', v::date('Y-m-d'))
                        ->key('end_date', v::date('Y-m-d'))
                        ->key('status', v::optional(v::boolVal()))
                        ->key('is_current', v::optional(v::boolVal()));

        try {
            $validator->assert($data);
            $period = $this->service->createPeriod($data, $teacherId);
            return $this->jsonResponse($response, ['status' => 'success', 'data' => $period], 201);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $e->getMessages()], 400);
        } catch (Exception $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        $validator = v::key('name', v::optional(v::stringType()->length(1, 50)))
                        ->key('start_date', v::optional(v::date('Y-m-d')))
                        ->key('end_date', v::optional(v::date('Y-m-d')))
                        ->key('status', v::optional(v::boolVal()))
                        ->key('is_current', v::optional(v::boolVal()));

        try {
            $validator->assert($data);
            $period = $this->service->updatePeriod($id, $data, $teacherId);
            if (!$period) {
                return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Period not found'], 404);
            }
            return $this->jsonResponse($response, ['status' => 'success', 'data' => $period]);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Datos inválidos', 'errors' => $e->getMessages()], 400);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];
        $deleted = $this->service->deletePeriod($id, $teacherId);

        if (!$deleted) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Period not found or unauthorized'], 404);
        }

        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Period deleted']);
    }

    public function setCurrent(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int) $args['id'];

        try {
            $period = $this->service->setCurrentPeriod($id, $teacherId);
            if (!$period) {
                return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Period not found'], 404);
            }

            return $this->jsonResponse($response, [
                'status' => 'success', 
                'message' => 'Periodo establecido como vigente',
                'data' => $period
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }
}
