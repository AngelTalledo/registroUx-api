<?php

declare(strict_types=1);

namespace App\Services;

interface EvaluationExcelServiceInterface
{
    /**
     * Generate an Excel report for student evaluations.
     *
     * @param int $teacherId
     * @param array $filters
     * @return string Binary content of the Excel file
     */
    public function generateEvaluationExcel(int $teacherId, array $filters): string;
}
