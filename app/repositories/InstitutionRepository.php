<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Collection;

class InstitutionRepository
{
    public function findAllByTeacher(int $teacherId): Collection
    {
        return Institution::where('teacher_id', $teacherId)
            ->with(['headerTemplate', 'logos'])
            ->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?Institution
    {
        return Institution::where('id', $id)
            ->where('teacher_id', $teacherId)
            ->with(['headerTemplate', 'logos'])
            ->first();
    }

    public function create(array $data): Institution
    {
        return Institution::create($data);
    }

    public function update(int $id, int $teacherId, array $data): ?Institution
    {
        $institution = $this->findByIdAndTeacher($id, $teacherId);
        if ($institution) {
            $institution->update($data);
            return $institution;
        }
        return null;
    }

    public function delete(int $id, int $teacherId): bool
    {
        $institution = $this->findByIdAndTeacher($id, $teacherId);
        if ($institution) {
            return $institution->delete();
        }
        return false;
    }
}
