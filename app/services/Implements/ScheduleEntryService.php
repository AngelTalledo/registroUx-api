<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\ScheduleEntryServiceInterface;
use App\Repositories\ScheduleEntryRepository;
use Illuminate\Database\Eloquent\Collection;

class ScheduleEntryService implements ScheduleEntryServiceInterface
{
    private ScheduleEntryRepository $repository;

    public function __construct(ScheduleEntryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function saveSchedule(int $teacherId, int $academicPeriodId, array $entries): void
    {
        $this->repository->bulkSave($teacherId, $academicPeriodId, $entries);
    }

    public function getSchedule(int $teacherId, int $academicPeriodId): Collection
    {
        return $this->repository->findAllByTeacherAndPeriod($teacherId, $academicPeriodId);
    }

    public function getScheduleByDay(int $teacherId, int $academicPeriodId, int $dayOfWeek): Collection
    {
        return $this->repository->findByTeacherPeriodAndDay($teacherId, $academicPeriodId, $dayOfWeek);
    }
}
