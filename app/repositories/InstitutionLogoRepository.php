<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InstitutionLogo;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Collection;

class InstitutionLogoRepository
{
    public function findByInstitution(int $institutionId, int $teacherId): Collection
    {
        // First verify institution belongs to teacher
        $institution = Institution::where('id', $institutionId)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$institution) {
            return new Collection();
        }

        return InstitutionLogo::where('institution_id', $institutionId)->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?InstitutionLogo
    {
        return InstitutionLogo::whereHas('institution', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->where('id', $id)->first();
    }

    public function create(array $data): InstitutionLogo
    {
        return InstitutionLogo::create($data);
    }

    public function delete(int $id, int $teacherId): bool
    {
        $logo = $this->findByIdAndTeacher($id, $teacherId);
        if ($logo) {
            return (bool) $logo->delete();
        }
        return false;
    }
}
