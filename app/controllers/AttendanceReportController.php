<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AttendanceReportServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AttendanceReportController
{
    use Helpers\AuthHelperTrait;

    private AttendanceReportServiceInterface $service;
    private UserServiceInterface $userService;

    public function __construct(AttendanceReportServiceInterface $service, UserServiceInterface $userService)
    {
        $this->service = $service;
        $this->userService = $userService;
    }

    public function generateReport(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $filters = $request->getParsedBody();
        $orientation = $filters['orientation'] ?? 'landscape';

        // Basic validation
        $required = ['academic_year_id', 'grado_id', 'aula_id', 'curso_id', 'period_id'];
        foreach ($required as $field) {
            if (empty($filters[$field])) {
                return $this->jsonResponse($response, [
                    'status' => 'error',
                    'message' => "El campo {$field} es requerido"
                ], 400);
            }
        }

        try {
            $pdfContent = $this->service->generateAttendanceReport($teacherId, $filters, $orientation);

            $response->getBody()->write($pdfContent);
            return $response
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'inline; filename="reporte_asistencia.pdf"');
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
