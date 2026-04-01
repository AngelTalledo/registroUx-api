<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

interface ScheduleEntryServiceInterface
{
    public function saveSchedule(int $teacherId, int $academicPeriodId, array $entries): void;
    public function getSchedule(int $teacherId, int $academicPeriodId): Collection;
    public function getScheduleByDay(int $teacherId, int $academicPeriodId, int $dayOfWeek): Collection;
}
