<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\DiagnosticEvaluation;
use Illuminate\Database\Eloquent\Collection;

class DiagnosticEvaluationRepository
{
    public function findAllByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = DiagnosticEvaluation::with(['student', 'competency', 'course', 'classroom', 'period'])
            ->where('teacher_id', $teacherId);

        if (!empty($filters['period_id'])) {
            $query->where('period_id', $filters['period_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (!empty($filters['aula_id'])) {
            $query->where('aula_id', $filters['aula_id']);
        }

        return $query->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?DiagnosticEvaluation
    {
        return DiagnosticEvaluation::with(['student', 'competency', 'course', 'classroom', 'period'])
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    public function updateOrCreate(array $criteria, array $data): DiagnosticEvaluation
    {
        return DiagnosticEvaluation::updateOrCreate($criteria, $data);
    }

    public function create(array $data): DiagnosticEvaluation
    {
        return DiagnosticEvaluation::create($data);
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
