<?php

declare(strict_types=1);

namespace App\Services;

interface EvaluationReportServiceInterface
{
    /**
     * Generates a PDF report for evaluations (Registro Auxiliar).
     *
     * @param int $teacherId
     * @param array $filters
     * @param string $orientation 'portrait' or 'landscape'
     * @return string PDF content
     */
    public function generateEvaluationReport(int $teacherId, array $filters, string $orientation = 'landscape'): string;
}
