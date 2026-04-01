<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Models\InstitutionLogo;
use App\Models\Institution;
use App\Repositories\InstitutionLogoRepository;
use App\Services\InstitutionLogoServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class InstitutionLogoService implements InstitutionLogoServiceInterface
{
    private InstitutionLogoRepository $repository;

    public function __construct(InstitutionLogoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getLogosByInstitution(int $institutionId, int $teacherId): Collection
    {
        return $this->repository->findByInstitution($institutionId, $teacherId);
    }

    public function createLogo(array $data, int $teacherId): ?InstitutionLogo
    {
        // Verify ownership of the institution before creating
        $institution = Institution::where('id', $data['institution_id'])
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$institution) {
            return null;
        }

        return $this->repository->create($data);
    }

    public function deleteLogo(int $id, int $teacherId): bool
    {
        return $this->repository->delete($id, $teacherId);
    }
}
