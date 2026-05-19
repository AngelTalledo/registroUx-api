<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Collection;

class ClassroomRepository
{
    public function findAllByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = Classroom::where('teacher_id', $teacherId);
        
        if (!empty($filters['deleted']) && $filters['deleted'] === true) {
            $query->onlyTrashed();
        }

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (!empty($filters['grade_id'])) {
            $query->where('grade_id', $filters['grade_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('section', 'like', "%{$searchTerm}%")
                  ->orWhereHas('grade', function($qg) use ($searchTerm) {
                      $qg->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        return $query->with(['course', 'grade', 'academicYear'])
                     ->orderBy('grade_id', 'asc')
                     ->orderBy('section', 'asc')
                     ->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Classroom
    {
        return Classroom::withTrashed()
                        ->where('id', $id)
                        ->where('teacher_id', $teacherId)
                        ->with(['course', 'grade', 'academicYear'])
                        ->first();
    }

    public function findDuplicate(int $teacherId, int $academicYearId, int $gradeId, int $courseId, string $section): ?Classroom
    {
        return Classroom::where('teacher_id', $teacherId)
                        ->where('academic_year_id', $academicYearId)
                        ->where('grade_id', $gradeId)
                        ->where('course_id', $courseId)
                        ->where('section', $section)
                        ->first();
    }

    public function create(array $data): Classroom
    {
        return Classroom::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Classroom
    {
        $classroom = $this->findByIdAndTeacher($id, $teacherId);
        if ($classroom) {
            if ($classroom->trashed() && isset($data['status']) && $data['status']) {
                $classroom->restore();
            }
            $classroom->update($data);
        }
        return $classroom;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $classroom = $this->findByIdAndTeacher($id, $teacherId);
        if ($classroom) {
            $classroom->update(['status' => 0]);
            return (bool)$classroom->delete();
        }
        return false;
    }
}
