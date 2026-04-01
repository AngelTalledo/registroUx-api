<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;

class SessionRepository
{
    public function findAllByTeacher(int $teacherId): Collection
    {
        return Session::with(['course', 'grade', 'classroom', 'period'])
            ->where('teacher_id', $teacherId)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Session
    {
        return Session::with(['course', 'grade', 'classroom', 'period'])
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    public function create(array $data): Session
    {
        return Session::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Session
    {
        $session = $this->findByIdAndTeacher($id, $teacherId);
        if ($session) {
            $session->update($data);
        }
        return $session;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $session = $this->findByIdAndTeacher($id, $teacherId);
        if ($session) {
            return (bool) $session->delete();
        }
        return false;
    }

    public function findDeletedByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = Session::onlyTrashed()
            ->with(['course', 'grade', 'classroom', 'period'])
            ->where('teacher_id', $teacherId);

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }
        if (!empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }
        if (!empty($filters['grade_id'])) {
            $query->where('grade_id', $filters['grade_id']);
        }
        if (!empty($filters['period_id'])) {
            $query->where('period_id', $filters['period_id']);
        }

        return $query->orderBy('deleted_at', 'desc')->get();
    }

    public function restoreByTeacher(int $id, int $teacherId): ?Session
    {
        $session = Session::withTrashed()
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();

        if ($session && $session->trashed()) {
            $session->restore();
            return $session;
        }

        return null;
    }
}
