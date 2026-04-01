<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\CourseServiceInterface;
use App\Repositories\CourseRepository;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseService implements CourseServiceInterface
{
    private CourseRepository $repository;

    public function __construct(CourseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCoursesByTeacher(int $teacherId, bool $deleted = false): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $deleted);
    }

    public function getCourseById(int $id, int $teacherId): ?Course
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createCourse(array $data): Course
    {
        return $this->repository->create($data);
    }

    public function updateCourse(int $id, int $teacherId, array $data): ?Course
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteCourse(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }
}
