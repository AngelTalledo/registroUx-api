<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Models\Period;
use App\Models\AcademicYear;
use App\Repositories\PeriodRepository;
use App\Services\PeriodServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class PeriodService implements PeriodServiceInterface
{
    private PeriodRepository $repository;
    private \App\Repositories\AcademicYearRepository $yearRepository;

    public function __construct(
        PeriodRepository $repository, 
        \App\Repositories\AcademicYearRepository $yearRepository
    ) {
        $this->repository = $repository;
        $this->yearRepository = $yearRepository;
    }

    public function getPeriodsByYear(int $yearId, int $teacherId): Collection
    {
        $this->verifyYearOwnership($yearId, $teacherId);
        return $this->repository->findByAcademicYear($yearId);
    }

    public function getPeriodsForCurrentYear(int $teacherId): Collection
    {
        $currentYear = $this->yearRepository->findCurrentByTeacherAll($teacherId);
        if (!$currentYear) {
            return new Collection();
        }
        return $this->repository->findByAcademicYear($currentYear->id);
    }

    public function getPeriodById(int $id, int $teacherId): ?Period
    {
        $period = $this->repository->findById($id);
        if ($period) {
            $this->verifyYearOwnership($period->academic_year_id, $teacherId);
        }
        return $period;
    }

    public function createPeriod(array $data, int $teacherId): Period
    {
        $this->verifyYearOwnership($data['academic_year_id'], $teacherId);
        return $this->repository->create($data);
    }

    public function updatePeriod(int $id, array $data, int $teacherId): ?Period
    {
        $period = $this->getPeriodById($id, $teacherId);
        if (!$period) return null;

        return $this->repository->update($id, $data);
    }

    public function deletePeriod(int $id, int $teacherId): bool
    {
        $period = $this->getPeriodById($id, $teacherId);
        if (!$period) return false;

        return $this->repository->delete($id);
    }

    public function setCurrentPeriod(int $id, int $teacherId): ?Period
    {
        $period = $this->getPeriodById($id, $teacherId);
        if (!$period) return null;

        return $this->repository->markAsCurrent($id, $period->academic_year_id);
    }

    private function verifyYearOwnership(int $yearId, int $teacherId): void
    {
        $year = AcademicYear::find($yearId);
        if (!$year || $year->teacher_id !== $teacherId) {
            throw new Exception("Acceso no autorizado al año académico.");
        }
    }
}
