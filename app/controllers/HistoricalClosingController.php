<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\HistoricalClosingServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class HistoricalClosingController
{
    use Helpers\AuthHelperTrait;

    private HistoricalClosingServiceInterface $service;
    private UserServiceInterface $userService;
    private LoggerInterface $logger;

    public function __construct(HistoricalClosingServiceInterface $service, UserServiceInterface $userService, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function closePeriod(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();

        $validator = v::key('period_id', v::intVal())
                        ->key('course_id', v::intVal())
                        ->key('aula_id', v::intVal())
                        ->key('academic_year_id', v::intVal())
                        ->key('type', v::in(['evaluation', 'attendance', 'all']));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Datos inválidos',
                'errors' => $e->getMessages()
            ], 400);
        }

        try {
            $result = $this->service->closePeriod($teacherId, $data);
            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Cierre de periodo ejecutado correctamente',
                'details' => $result
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error closing period: ' . $e->getMessage());
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Error al ejecutar el cierre: ' . $e->getMessage()
            ], 500);
        }
    }

    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
