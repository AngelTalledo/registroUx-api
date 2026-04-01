<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseServiceInterface
{
    public function getAllCoursesByTeacher(int $teacherId, bool $deleted = false): Collection;
    public function getCourseById(int $id, int $teacherId): ?Course;
    public function createCourse(array $data): Course;
    public function updateCourse(int $id, int $teacherId, array $data): ?Course;
    public function deleteCourse(int $id, int $teacherId): bool;
}
