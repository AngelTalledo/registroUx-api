<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

interface StudentServiceInterface
{
    public function getAllStudentsByTeacher(int $teacherId, array $filters = []): Collection;
    public function getStudentById(int $id, int $teacherId): ?Student;
    public function createStudent(array $data): Student;
    public function updateStudent(int $id, int $teacherId, array $data): ?Student;
    public function deleteStudent(int $id, int $teacherId): bool;
    public function getMyCourses(int $teacherId): Collection;
    public function bulkStoreStudents(int $teacherId, array $data): array;
}
