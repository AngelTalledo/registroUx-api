<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\SessionCompetency;
use Illuminate\Database\Eloquent\Collection;

class SessionCompetencyRepository
{
    public function findAllByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = SessionCompetency::with(['competency'])
            ->where('teacher_id', $teacherId);


        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        return $query->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?SessionCompetency
    {
        return SessionCompetency::with(['competency'])
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    public function create(array $data): SessionCompetency
    {
        return SessionCompetency::create($data);
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $record = $this->findByIdAndTeacher($id, $teacherId);
        if ($record) {
            return (bool) $record->delete();
        }
        return false;
    }

    public function restoreByTeacher(int $id, int $teacherId): bool
    {
        $record = SessionCompetency::onlyTrashed()
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
            
        if ($record) {
            return (bool) $record->restore();
        }
        return false;
    }

    public function restoreByCompetency(int $competencyId, int $teacherId): bool
    {
        return (bool) SessionCompetency::onlyTrashed()
            ->where('competency_id', $competencyId)
            ->where('teacher_id', $teacherId)
            ->restore();
    }

    public function findDeletedByTeacher(int $teacherId, int $academicYearId): Collection
    {
        return SessionCompetency::onlyTrashed()
            ->with(['competency', 'period', 'course', 'grade', 'classroom'])
            ->where('teacher_id', $teacherId)
            ->whereHas('period', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            })
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

}
