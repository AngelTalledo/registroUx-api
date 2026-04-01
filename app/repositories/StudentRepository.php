<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository
{
    public function findAllByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = Student::with(['course', 'grade', 'classroom'])
            ->where('teacher_id', $teacherId);
        
        if (!empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }
        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }
        if (!empty($filters['grade_id'])) {
            $query->where('grade_id', $filters['grade_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['deleted'])) {
            $query->onlyTrashed();
        }

        return $query->orderBy('last_names', 'asc')->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Student
    {
        return Student::withTrashed()
                      ->where('id', $id)
                      ->where('teacher_id', $teacherId)
                      ->first();
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Student
    {
        $student = $this->findByIdAndTeacher($id, $teacherId);
        if ($student) {
            if ($student->trashed() && isset($data['status']) && $data['status']) {
                $student->restore();
            }
            $student->update($data);
        }
        return $student;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $student = $this->findByIdAndTeacher($id, $teacherId);
        if ($student) {
            return (bool) $student->delete();
        }
        return false;
    }

    public function findMyCourses(int $teacherId): Collection
    {
        return Student::select(
                'courses.id as course_id', 
                'courses.name as course_name',
                'grades.id as grade_id', 
                'grades.name as grade_name',
                'classrooms.id as classroom_id', 
                'classrooms.section as classroom_section',
                'periods.id as period_id',
                'periods.name as period_name'
            )
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->join('academic_years', 'classrooms.academic_year_id', '=', 'academic_years.id')
            ->join('periods', 'academic_years.id', '=', 'periods.academic_year_id')
            ->join('courses', 'students.course_id', '=', 'courses.id')
            ->join('grades', 'students.grade_id', '=', 'grades.id')
            ->where('students.teacher_id', $teacherId)
            ->where('academic_years.is_current', true)
            ->where('periods.is_current', true)
            ->distinct()
            ->get();
    }
}
