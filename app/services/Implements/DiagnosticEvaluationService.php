<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Models\DiagnosticEvaluation;
use App\Repositories\DiagnosticEvaluationRepository;
use App\Services\DiagnosticEvaluationServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class DiagnosticEvaluationService implements DiagnosticEvaluationServiceInterface
{
    private DiagnosticEvaluationRepository $repository;

    public function __construct(DiagnosticEvaluationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEvaluationsByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getEvaluationById(int $id, int $teacherId): ?DiagnosticEvaluation
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createEvaluation(array $data): DiagnosticEvaluation
    {
        return $this->repository->create($data);
    }

    public function upsertEvaluation(array $data): DiagnosticEvaluation
    {
        $criteria = [
            'teacher_id' => $data['teacher_id'],
            'period_id' => $data['period_id'],
            'student_id' => $data['student_id'],
            'competency_id' => $data['competency_id'],
            'course_id' => $data['course_id'],
            'aula_id' => $data['aula_id'],
        ];

        return $this->repository->updateOrCreate($criteria, $data);
    }

    public function deleteEvaluation(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }
}
