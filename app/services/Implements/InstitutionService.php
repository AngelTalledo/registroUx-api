<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Models\Institution;
use App\Repositories\InstitutionRepository;
use App\Services\InstitutionServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class InstitutionService implements InstitutionServiceInterface
{
    private InstitutionRepository $repository;

    public function __construct(InstitutionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllInstitutions(int $teacherId): Collection
    {
        return $this->repository->findAllByTeacher($teacherId);
    }

    public function getInstitutionById(int $id, int $teacherId): ?Institution
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createInstitution(array $data): Institution
    {
        return $this->repository->create($data);
    }

    public function updateInstitution(int $id, int $teacherId, array $data): ?Institution
    {
        return $this->repository->update($id, $teacherId, $data);
    }

    public function deleteInstitution(int $id, int $teacherId): bool
    {
        return $this->repository->delete($id, $teacherId);
    }
}
