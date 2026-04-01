<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\SessionCompetencyServiceInterface;
use App\Repositories\SessionCompetencyRepository;
use App\Repositories\AcademicYearRepository;
use App\Repositories\CompetencyRepository;
use App\Models\SessionCompetency;
use Illuminate\Database\Eloquent\Collection;

class SessionCompetencyService implements SessionCompetencyServiceInterface
{
    private SessionCompetencyRepository $repository;
    private AcademicYearRepository $academicYearRepository;
    private CompetencyRepository $competencyRepository;

    public function __construct(
        SessionCompetencyRepository $repository, 
        AcademicYearRepository $academicYearRepository,
        CompetencyRepository $competencyRepository
    ) {
        $this->repository = $repository;
        $this->academicYearRepository = $academicYearRepository;
        $this->competencyRepository = $competencyRepository;
    }

    public function getAllSessionCompetenciesByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getSessionCompetencyById(int $id, int $teacherId): ?SessionCompetency
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createSessionCompetency(array $data): SessionCompetency
    {
        return $this->repository->create($data);
    }

    public function deleteSessionCompetency(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getDeletedSessionsByTeacher(int $teacherId): array
    {
        $currentYear = $this->academicYearRepository->findCurrentByTeacherAll($teacherId);
        if (!$currentYear) {
            return [];
        }

        // Fetch deleted records withwithTrashed competency
        $deletedRecords = SessionCompetency::onlyTrashed()
            ->with(['competency' => function ($query) {
                $query->withTrashed();
            }])
            ->where('teacher_id', $teacherId)
            ->whereHas('period', function ($query) use ($currentYear) {
                $query->where('academic_year_id', $currentYear->id);
            })
            ->get();

        $grouped = [];
        $sessionsCount = [];

        foreach ($deletedRecords as $record) {
            $compId = $record->competency_id;
            
            if (!isset($grouped[$compId])) {
                $comp = $record->competency;
                $grouped[$compId] = [
                    'id' => $compId,
                    'type' => 'competency',
                    'label' => "Competencia {$compId}: " . ($comp ? $comp->name : 'Desconocida'),
                    'deletedAt' => $comp && $comp->trashed() ? $comp->deleted_at->toISOString() : null,
                    'sessions' => []
                ];
                $sessionsCount[$compId] = 0;
            }

            $sessionsCount[$compId]++;
            $grouped[$compId]['sessions'][] = [
                'id' => $record->id,
                'type' => 'session',
                'label' => 'S' . $sessionsCount[$compId],
                'theme' => $record->theme ?? '',
                'deletedAt' => $record->deleted_at->toISOString()
            ];
        }

        return array_values($grouped);
    }

    public function handleUnifiedRestore(array $data, int $teacherId): bool
    {
        $id = (int)$data['id'];
        $type = $data['type'] ?? '';

        if ($type === 'session') {
            return $this->repository->restoreByTeacher($id, $teacherId);
        }

        if ($type === 'competency') {
            $restored = $this->competencyRepository->restoreByTeacher($id, $teacherId);
            
            if ($restored && !empty($data['includeSessions'])) {
                $this->repository->restoreByCompetency($id, $teacherId);
            }
            
            return $restored;
        }

        return false;
    }
}
