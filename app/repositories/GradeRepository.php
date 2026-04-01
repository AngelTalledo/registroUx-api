<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

class GradeRepository
{
    public function findAllByTeacher(int $teacherId, bool $deleted = false): Collection
    {
        $query = Grade::where('teacher_id', $teacherId);
        if ($deleted) {
            $query->onlyTrashed();
        }
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Grade
    {
        return Grade::withTrashed()
                    ->where('id', $id)
                    ->where('teacher_id', $teacherId)
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    public function create(array $data): Grade
    {
        return Grade::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Grade
    {
        $grade = $this->findByIdAndTeacher($id, $teacherId);
        if ($grade) {
            if ($grade->trashed() && isset($data['status']) && $data['status']) {
                $grade->restore();
            }
            $grade->update($data);
        }
        return $grade;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $grade = $this->findByIdAndTeacher($id, $teacherId);
        if ($grade) {
            $grade->update(['status' => 0]);
            return (bool) $grade->delete();
        }
        return false;
    }
}
