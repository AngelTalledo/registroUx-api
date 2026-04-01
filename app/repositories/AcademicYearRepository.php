<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Collection;

class AcademicYearRepository
{
    public function findAll(): Collection
    {
        return AcademicYear::all();
    }

    public function findAllByTeacher(int $teacherId): Collection
    {
        return AcademicYear::where('teacher_id', $teacherId)
                            ->where('status', 1)
                            ->orderBy('created_at', 'desc')
                            ->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?AcademicYear
    {
        return AcademicYear::where('id', $id)
                          ->where('teacher_id', $teacherId)
                          ->first();
    }

    public function create(array $data): AcademicYear
    {
        return AcademicYear::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?AcademicYear
    {
        $academicYear = $this->findByIdAndTeacher($id, $teacherId);
        if ($academicYear) {
            $academicYear->update($data);
        }
        return $academicYear;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $academicYear = $this->findByIdAndTeacher($id, $teacherId);
        return $academicYear ? $academicYear->delete() : false;
    }

    public function markAsCurrent(int $id, int $teacherId): ?AcademicYear
    {
        // 1. Reset all academic years for this teacher to is_current = 0
        AcademicYear::where('teacher_id', $teacherId)
                    ->update(['is_current' => 0]);

        // 2. Fetch the target academic year
        $academicYear = $this->findByIdAndTeacher($id, $teacherId);

        if ($academicYear) {
            // 3. Set to 1 (active)
            $academicYear->update(['is_current' => 1]);
        }

        return $academicYear;
    }

    public function findCurrentByTeacher(int $teacherId): ?AcademicYear
    {
        return AcademicYear::where('teacher_id', $teacherId)
                           ->where('is_current', 1)
                           ->first();
    }

    public function findCurrentByTeacherAll(int $teacherId): ?AcademicYear
    {
        return AcademicYear::where('teacher_id', $teacherId)
                           ->orderBy('is_current', 'desc')
                           ->orderBy('id', 'desc')
                           ->first();
    }
}
