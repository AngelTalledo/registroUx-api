<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository
{
    public function findAll(): Collection
    {
        return Teacher::with('user')->get();
    }

    public function findById(int $id): ?Teacher
    {
        return Teacher::with('user')->find($id);
    }

    public function create(array $data): Teacher
    {
        return Teacher::create($data);
    }

    public function update(int $id, array $data): ?Teacher
    {
        $teacher = Teacher::find($id);
        if ($teacher) {
            $teacher->update($data);
        }
        return $teacher;
    }

    public function delete(int $id): bool
    {
        $teacher = Teacher::find($id);
        return $teacher ? $teacher->delete() : false;
    }

    /**
     * Busca docentes por nombre o email con perfil completo cargado
     */
    public function searchFullProfile(string $query): Collection
    {
        return Teacher::with([
            'user',
            'academicYears.periods',
            'classrooms.course',
            'classrooms.grade',
            'competencies.course'
        ])
        ->where(function ($q) use ($query) {
            $q->where('full_name', 'like', "%$query%")
              ->orWhereHas('user', function ($uq) use ($query) {
                  $uq->where('email', 'like', "%$query%");
              });
        })
        ->limit(15) // Limitamos para evitar sobrecarga en búsquedas ambiguas
        ->get();
    }
}
