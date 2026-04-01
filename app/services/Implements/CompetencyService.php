<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\CompetencyServiceInterface;
use App\Repositories\CompetencyRepository;
use App\Models\Competency;
use Illuminate\Database\Eloquent\Collection;

class CompetencyService implements CompetencyServiceInterface
{
    private CompetencyRepository $repository;

    public function __construct(CompetencyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCompetenciesByTeacher(int $teacherId, ?int $academicYearId = null, bool $deleted = false, ?array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $academicYearId, $deleted, $filters);
    }

    public function getCompetencyById(int $id, int $teacherId): ?Competency
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createCompetency(array $data): Competency
    {
        return $this->repository->create($data);
    }

    public function updateCompetency(int $id, int $teacherId, array $data): ?Competency
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteCompetency(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }
}
