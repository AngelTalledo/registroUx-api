<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Competency;
use Illuminate\Database\Eloquent\Collection;

interface CompetencyServiceInterface
{
    public function getAllCompetenciesByTeacher(int $teacherId, ?int $academicYearId = null, bool $deleted = false, ?array $filters = []): Collection;
    public function getCompetencyById(int $id, int $teacherId): ?Competency;
    public function createCompetency(array $data): Competency;
    public function updateCompetency(int $id, int $teacherId, array $data): ?Competency;
    public function deleteCompetency(int $id, int $teacherId): bool;
}
