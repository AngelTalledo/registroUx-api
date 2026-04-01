<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Collection;

interface InstitutionServiceInterface
{
    public function getAllInstitutions(int $teacherId): Collection;
    public function getInstitutionById(int $id, int $teacherId): ?Institution;
    public function createInstitution(array $data): Institution;
    public function updateInstitution(int $id, int $teacherId, array $data): ?Institution;
    public function deleteInstitution(int $id, int $teacherId): bool;
}
