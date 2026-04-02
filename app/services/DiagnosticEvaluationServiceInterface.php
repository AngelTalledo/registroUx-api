<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DiagnosticEvaluation;
use Illuminate\Database\Eloquent\Collection;

interface DiagnosticEvaluationServiceInterface
{
    public function getAllEvaluationsByTeacher(int $teacherId, array $filters = []): Collection;
    public function getEvaluationById(int $id, int $teacherId): ?DiagnosticEvaluation;
    public function createEvaluation(array $data): DiagnosticEvaluation;
    public function upsertEvaluation(array $data): DiagnosticEvaluation;
    public function deleteEvaluation(int $id, int $teacherId): bool;
}
