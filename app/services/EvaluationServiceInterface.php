<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Collection;

interface EvaluationServiceInterface
{
    public function getAllEvaluationsByTeacher(int $teacherId, array $filters = []): Collection;
    public function getEvaluationById(int $id, int $teacherId): ?Evaluation;
    public function createEvaluation(array $data): Evaluation;
    public function updateEvaluation(int $id, int $teacherId, array $data): ?Evaluation;
    public function deleteEvaluation(int $id, int $teacherId): bool;
    public function upsertEvaluation(array $data): Evaluation;
    public function getEvaluationReport(int $teacherId, int $courseId, int $gradeId, int $classroomId): array;
}
