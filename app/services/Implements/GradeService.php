<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\GradeServiceInterface;
use App\Repositories\GradeRepository;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

class GradeService implements GradeServiceInterface
{
    private GradeRepository $repository;

    public function __construct(GradeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllGradesByTeacher(int $teacherId, bool $deleted = false): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $deleted);
    }

    public function getGradeById(int $id, int $teacherId): ?Grade
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createGrade(array $data): Grade
    {
        return $this->repository->create($data);
    }

    public function updateGrade(int $id, int $teacherId, array $data): ?Grade
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteGrade(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }
}
