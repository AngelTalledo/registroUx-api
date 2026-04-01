<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

interface GradeServiceInterface
{
    public function getAllGradesByTeacher(int $teacherId, bool $deleted = false): Collection;
    public function getGradeById(int $id, int $teacherId): ?Grade;
    public function createGrade(array $data): Grade;
    public function updateGrade(int $id, int $teacherId, array $data): ?Grade;
    public function deleteGrade(int $id, int $teacherId): bool;
}
