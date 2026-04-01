<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\EvaluationReportServiceInterface;
use App\Services\EvaluationExcelServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EvaluationReportController
{
    use Helpers\AuthHelperTrait;

    private EvaluationReportServiceInterface $service;
    private EvaluationExcelServiceInterface $excelService;
    private UserServiceInterface $userService;

    public function __construct(
        EvaluationReportServiceInterface $service, 
        EvaluationExcelServiceInterface $excelService,
        UserServiceInterface $userService
    ) {
        $this->service = $service;
        $this->excelService = $excelService;
        $this->userService = $userService;
    }

    public function generateReport(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $filters = $request->getParsedBody();
        $orientation = $filters['orientation'] ?? 'landscape';
        $format = $filters['format'] ?? 'pdf';

        // Basic validation
        $required = ['academic_year_id', 'grado_id', 'aula_id', 'curso_id', 'period_id'];
        foreach ($required as $field) {
            if (empty($filters[$field])) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => "El campo {$field} es requerido"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            if ($format === 'excel') {
                $content = $this->excelService->generateEvaluationExcel($teacherId, $filters);
                $response->getBody()->write($content);
                return $response
                    ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->withHeader('Content-Disposition', 'attachment; filename="registro_auxiliar.xlsx"')
                    ->withStatus(200);
            }

            $pdfContent = $this->service->generateEvaluationReport($teacherId, $filters, $orientation);

            $response->getBody()->write($pdfContent);
            return $response
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'inline; filename="registro_auxiliar.pdf"');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
