<?php

declare(strict_types=1);

namespace App\Services;

interface AttendanceReportServiceInterface
{
    public function generateAttendanceReport(int $teacherId, array $filters, string $orientation = 'landscape'): string;
}
