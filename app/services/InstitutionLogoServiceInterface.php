<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InstitutionLogo;
use Illuminate\Database\Eloquent\Collection;

interface InstitutionLogoServiceInterface
{
    public function getLogosByInstitution(int $institutionId, int $teacherId): Collection;
    public function createLogo(array $data, int $teacherId): ?InstitutionLogo;
    public function deleteLogo(int $id, int $teacherId): bool;
}
