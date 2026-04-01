<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\TeacherServiceInterface;
use App\Repositories\TeacherRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Teacher;

class TeacherService implements TeacherServiceInterface
{
    private TeacherRepository $repository;

    public function __construct(TeacherRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTeachers(): Collection
    {
        return $this->repository->findAll();
    }

    public function getTeacherById(int $id): ?Teacher
    {
        return $this->repository->findById($id);
    }

    public function createTeacher(array $data): Teacher
    {
        return $this->repository->create($data);
    }

    public function updateTeacher(int $id, array $data): ?Teacher
    {
        return $this->repository->update($id, $data);
    }
}
