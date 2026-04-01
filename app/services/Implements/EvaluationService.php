<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\EvaluationServiceInterface;
use App\Repositories\EvaluationRepository;
use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Collection;

class EvaluationService implements EvaluationServiceInterface
{
    private EvaluationRepository $repository;

    public function __construct(EvaluationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEvaluationsByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getEvaluationById(int $id, int $teacherId): ?Evaluation
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createEvaluation(array $data): Evaluation
    {
        return $this->repository->create($data);
    }

    public function upsertEvaluation(array $data): Evaluation
    {
        $criteria = [
            'teacher_id' => $data['teacher_id'],
            'student_id' => $data['student_id'],
            'session_competency_id' => $data['session_competency_id'],
        ];

        return $this->repository->updateOrCreate($criteria, $data);
    }

    public function updateEvaluation(int $id, int $teacherId, array $data): ?Evaluation
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteEvaluation(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getEvaluationReport(int $teacherId, int $courseId, int $gradeId, int $classroomId): array
    {
        // 1. Obtenemos todas las competencias del profesor
        $allCompetencies = \App\Models\Competency::where('teacher_id', $teacherId)->get();

        // 2. Obtenemos las sesiones-competencia para este curso/aula/grado
        $sessionCompetencies = \App\Models\SessionCompetency::with(['competency'])
            ->where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('grade_id', $gradeId)
            ->where('classroom_id', $classroomId)
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Agrupamos sesiones por competencia para el mapeo
        $sessionsByCompetency = $sessionCompetencies->groupBy('competency_id');
        
        $competenciesReport = [];
        $sessionsCount = 0;
        
        foreach ($allCompetencies as $c) {
            $sessions = [];
            if ($sessionsByCompetency->has($c->id)) {
                foreach ($sessionsByCompetency->get($c->id) as $sc) {
                    $sessionsCount++;
                    $sessions[] = [
                        'id' => $sc->id,
                        'label' => 'S' . $sessionsCount,
                        'date' => $sc->date ?? ''
                    ];
                }
            }
            
            $competenciesReport[] = [
                'id' => $c->id,
                'name' => $c->name,
                'description' => '', // No hay descripción en el modelo actual
                'sessions' => $sessions
            ];
        }

        // 4. Obtenemos la lista de estudiantes
        $students = \App\Models\Student::where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('grade_id', $gradeId)
            ->where('classroom_id', $classroomId)
            ->orderBy('last_names', 'asc')
            ->get();

        $reportStudents = $students->map(function ($student) use ($sessionCompetencies) {
            // Buscamos todas las evaluaciones del estudiante con sus evidencias
            $evaluations = \App\Models\Evaluation::with('evidences')
                ->where('student_id', $student->id)
                ->whereIn('session_competency_id', $sessionCompetencies->pluck('id'))
                ->get()
                ->keyBy('session_competency_id');

            // Mapeamos cada columna (session_competency)
            $studentEvaluations = $sessionCompetencies->map(function ($sc) use ($evaluations) {
                $eval = $evaluations->get($sc->id);
                
                $evidences = [];
                if ($eval && $eval->evidences) {
                    foreach ($eval->evidences as $evidence) {
                        $evidences[] = [
                            'id' => $evidence->id,
                            'url' => $evidence->file_url,
                            'timestamp' => $evidence->created_at ? $evidence->created_at->format('h:i A') : ''
                        ];
                    }
                }

                return [
                    'session_competency_id' => $sc->id,
                    'evaluations' => $eval ? [['grade' => $eval->grade]] : [],
                    'evidence' => $evidences
                ];
            });

            return [
                'id' => $student->id,
                'names' => $student->names,
                'last_names' => $student->last_names,
                'is_exonerated' => $student->is_exonerated,
                'evaluations' => $studentEvaluations
            ];
        });

        return [
            'competencies' => $competenciesReport,
            'students' => $reportStudents
        ];
    }
}
