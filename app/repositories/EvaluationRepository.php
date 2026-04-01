<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Collection;

class EvaluationRepository
{
    public function findAllByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = Evaluation::with(['student', 'sessionCompetency'])
            ->where('teacher_id', $teacherId);

        if (!empty($filters['session_competency_id'])) {
            $query->where('session_competency_id', $filters['session_competency_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Evaluation
    {
        return Evaluation::with(['student', 'sessionCompetency', 'evidences'])
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    public function updateOrCreate(array $criteria, array $data): Evaluation
    {
        return Evaluation::updateOrCreate($criteria, $data);
    }

    public function create(array $data): Evaluation
    {
        return Evaluation::create($data);
    }

    public function updateByTeacher(int $id, int $teacherId, array $data): ?Evaluation
    {
        $evaluation = $this->findByIdAndTeacher($id, $teacherId);
        if ($evaluation) {
            $evaluation->update($data);
        }
        return $evaluation;
    }

    public function deleteByTeacher(int $id, int $teacherId): bool
    {
        $evaluation = $this->findByIdAndTeacher($id, $teacherId);
        if ($evaluation) {
            return (bool) $evaluation->delete();
        }
        return false;
    }
}
