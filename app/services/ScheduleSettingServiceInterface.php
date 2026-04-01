<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ScheduleSetting;

interface ScheduleSettingServiceInterface
{
    public function getSettings(int $teacherId, int $academicYearId): ?ScheduleSetting;
    public function updateSettings(array $data): ScheduleSetting;
}
