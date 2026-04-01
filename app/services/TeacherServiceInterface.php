<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

interface TeacherServiceInterface
{
    public function getAllTeachers(): Collection;
    public function getTeacherById(int $id): ?Teacher;
    public function createTeacher(array $data): Teacher;
    public function updateTeacher(int $id, array $data): ?Teacher;
}
