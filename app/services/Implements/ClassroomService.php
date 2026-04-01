<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\ClassroomServiceInterface;
use App\Repositories\ClassroomRepository;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Collection;

class ClassroomService implements ClassroomServiceInterface
{
    private ClassroomRepository $repository;

    public function __construct(ClassroomRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllClassroomsByTeacher(int $teacherId, bool $deleted = false): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $deleted);
    }

    public function getClassroomById(int $id, int $teacherId): ?Classroom
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createClassroom(array $data): Classroom
    {
        return $this->repository->create($data);
    }

    public function updateClassroom(int $id, int $teacherId, array $data): ?Classroom
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteClassroom(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }
}
