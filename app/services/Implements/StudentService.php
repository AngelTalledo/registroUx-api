<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\StudentServiceInterface;
use App\Repositories\StudentRepository;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class StudentService implements StudentServiceInterface
{
    private StudentRepository $repository;

    public function __construct(StudentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllStudentsByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getStudentById(int $id, int $teacherId): ?Student
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createStudent(array $data): Student
    {
        return $this->repository->create($data);
    }

    public function updateStudent(int $id, int $teacherId, array $data): ?Student
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteStudent(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getMyCourses(int $teacherId): Collection
    {
        return $this->repository->findMyCourses($teacherId);
    }
}
