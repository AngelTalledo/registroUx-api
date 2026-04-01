<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\AcademicYearServiceInterface;
use App\Repositories\AcademicYearRepository;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Collection;

class AcademicYearService implements AcademicYearServiceInterface
{
    private AcademicYearRepository $repository;

    public function __construct(AcademicYearRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllAcademicYears(?int $teacherId = null): Collection
    {
        if ($teacherId === null) {
            return $this->repository->findAll();
        }
        return $this->repository->findAllByTeacher($teacherId);
    }

    public function getAcademicYearById(int $id, int $teacherId): ?AcademicYear
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createAcademicYear(array $data): AcademicYear
    {
        return $this->repository->create($data);
    }

    public function updateAcademicYear(int $id, int $teacherId, array $data): ?AcademicYear
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteAcademicYear(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function setCurrentYear(int $id, int $teacherId): ?AcademicYear
    {
        return $this->repository->markAsCurrent($id, $teacherId);
    }
}
