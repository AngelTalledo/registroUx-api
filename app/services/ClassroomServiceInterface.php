<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Collection;

interface ClassroomServiceInterface
{
    public function getAllClassroomsByTeacher(int $teacherId, array $filters = []): Collection;
    public function getClassroomById(int $id, int $teacherId): ?Classroom;
    public function checkDuplicate(int $teacherId, int $academicYearId, int $gradeId, int $courseId, string $section): ?Classroom;
    public function createClassroom(array $data): Classroom;
    public function updateClassroom(int $id, int $teacherId, array $data): ?Classroom;
    public function deleteClassroom(int $id, int $teacherId): bool;
}
