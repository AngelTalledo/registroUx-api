<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Collection;

class ClassroomRepository
{
    public function findAllByTeacher(int $teacherId, bool $deleted = false): Collection
    {
        $query = Classroom::where('teacher_id', $teacherId);
        if ($deleted) {
            $query->onlyTrashed();
        }
        return $query->with(['course', 'grade', 'academicYear'])->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Classroom
    {
        return Classroom::withTrashed()
                        ->where('id', $id)
                        ->where('teacher_id', $teacherId)
                        ->with(['course', 'grade', 'academicYear'])
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
