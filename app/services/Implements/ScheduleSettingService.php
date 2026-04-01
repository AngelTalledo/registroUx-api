<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\ScheduleSettingServiceInterface;
use App\Repositories\ScheduleSettingRepository;
use App\Models\ScheduleSetting;

class ScheduleSettingService implements ScheduleSettingServiceInterface
{
    private ScheduleSettingRepository $repository;

    public function __construct(ScheduleSettingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getSettings(int $teacherId, int $academicYearId): ?ScheduleSetting
    {
        return $this->repository->findByTeacherAndYear($teacherId, $academicYearId);
    }

    public function updateSettings(array $data): ScheduleSetting
    {
        return $this->repository->upsert($data);
    }
}
