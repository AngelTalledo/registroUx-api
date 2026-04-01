<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SessionCompetency;
use Illuminate\Database\Eloquent\Collection;

interface SessionCompetencyServiceInterface
{
    public function getAllSessionCompetenciesByTeacher(int $teacherId, array $filters = []): Collection;
    public function getSessionCompetencyById(int $id, int $teacherId): ?SessionCompetency;
    public function createSessionCompetency(array $data): SessionCompetency;
    public function deleteSessionCompetency(int $id, int $teacherId): bool;
    public function getDeletedSessionsByTeacher(int $teacherId): array;
    public function handleUnifiedRestore(array $data, int $teacherId): bool;
}
