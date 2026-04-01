<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository
{
    public function findAllByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = Attendance::with(['student', 'session'])
            ->where('teacher_id', $teacherId);

        if (!empty($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Attendance
    {
        return Attendance::with(['student', 'session'])
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Attendance
    {
        $attendance = $this->findByIdAndTeacher($id, $teacherId);
        if ($attendance) {
            $attendance->update($data);
        }
        return $attendance;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $attendance = $this->findByIdAndTeacher($id, $teacherId);
        if ($attendance) {
            return (bool) $attendance->delete();
        }
        return false;
    }
}
