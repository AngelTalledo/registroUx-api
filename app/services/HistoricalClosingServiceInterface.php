<?php

declare(strict_types=1);

namespace App\Services;

interface HistoricalClosingServiceInterface
{
    /**
     * Executes the period closing for evaluations or attendance.
     * 
     * @param int $teacherId
     * @param array $data Contains period_id, course_id, classroom_id, academic_year_id, type
     * @return array Summary of the operation
     */
    public function closePeriod(int $teacherId, array $data): array;
}
