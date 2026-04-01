<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Competency;
use Illuminate\Database\Eloquent\Collection;

class CompetencyRepository
{
    public function findAllByTeacher(int $teacherId, ?int $academicYearId = null, bool $deleted = false, ?array $filters = []): Collection
    {
        $query = Competency::where('teacher_id', $teacherId);
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', (bool) $filters['status']);
        }

        if ($deleted) {
            $query->onlyTrashed();
        }

        return $query->with('course')->orderBy('created_at', 'desc')->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Competency
    {
        return Competency::withTrashed()
                        ->where('id', $id)
                        ->where('teacher_id', $teacherId)
                        ->first();
    }

    public function create(array $data): Competency
    {
        return Competency::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Competency
    {
        $competency = $this->findByIdAndTeacher($id, $teacherId);
        if ($competency) {
            if ($competency->trashed() && isset($data['status']) && $data['status']) {
                $competency->restore();
            }
            $competency->update($data);
        }
        return $competency;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $competency = $this->findByIdAndTeacher($id, $teacherId);
        if ($competency) {
            return (bool) $competency->delete();
        }
        return false;
    }

    public function restoreByTeacher(int $id, int $teacherId): bool
    {
        $competency = Competency::onlyTrashed()
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();

        if ($competency) {
            return (bool) $competency->restore();
        }
        return false;
    }
}
