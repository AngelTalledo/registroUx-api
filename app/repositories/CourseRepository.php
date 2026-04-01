<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository
{
    public function findAllByTeacher(int $teacherId, bool $deleted = false): Collection
    {
        $query = Course::where('teacher_id', $teacherId);
        if ($deleted) {
            $query->onlyTrashed();
        }
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Course
    {
        return Course::withTrashed()
                     ->where('id', $id)
                     ->where('teacher_id', $teacherId)
                     ->orderBy('created_at', 'desc')
                     ->first();
    }

    public function create(array $data): Course
    {
        return Course::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Course
    {
        $course = $this->findByIdAndTeacher($id, $teacherId);
        if ($course) {
            if ($course->trashed() && isset($data['status']) && $data['status']) {
                $course->restore();
            }
            $course->update($data);
        }
        return $course;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $course = $this->findByIdAndTeacher($id, $teacherId);
        if ($course) {
            $course->update(['status' => 0]);
            return $course->delete();
        }
        return false;
    }
}
