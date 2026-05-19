<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Models\DiagnosticEvaluation;
use App\Repositories\DiagnosticEvaluationRepository;
use App\Services\DiagnosticEvaluationServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class DiagnosticEvaluationService implements DiagnosticEvaluationServiceInterface
{
    private DiagnosticEvaluationRepository $repository;

    public function __construct(DiagnosticEvaluationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEvaluationsByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getEvaluationById(int $id, int $teacherId): ?DiagnosticEvaluation
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createEvaluation(array $data): DiagnosticEvaluation
    {
        return $this->repository->create($data);
    }

    private function validateActivePeriod(int $teacherId, int $periodId, int $studentId): int
    {
        $period = \App\Models\Period::find($periodId);
        if (!$period) {
            throw new \InvalidArgumentException("El periodo seleccionado no existe.");
        }

        // 1. Validar que el año académico del periodo pertenezca al docente logueado
        $academicYear = \App\Models\AcademicYear::find($period->academic_year_id);
        if (!$academicYear || (int)$academicYear->teacher_id !== $teacherId) {
            throw new \InvalidArgumentException("El año académico del periodo seleccionado no corresponde al docente.");
        }

        // 2. Validar consistencia: El año académico del aula del estudiante debe coincidir con el del periodo
        $student = \App\Models\Student::with('classroom')->find($studentId);
        if ($student && $student->classroom) {
            if ((int)$student->classroom->academic_year_id !== (int)$period->academic_year_id) {
                throw new \InvalidArgumentException("El año académico de la sección del estudiante no coincide con el año académico del periodo seleccionado.");
            }
        }

        return (int)$period->academic_year_id;
    }

    public function upsertEvaluation(array $data): DiagnosticEvaluation
    {
        $academicYearId = $this->validateActivePeriod((int)$data['teacher_id'], (int)$data['period_id'], (int)$data['student_id']);

        $criteria = [
            'teacher_id' => $data['teacher_id'],
            'academic_year_id' => $academicYearId,
            'period_id' => $data['period_id'],
            'student_id' => $data['student_id'],
            'competency_id' => $data['competency_id'],
            'course_id' => $data['course_id'],
            'aula_id' => $data['aula_id'],
        ];

        $grade = $data['grade'] ?? null;

        if ($grade === null || $grade === '') {
            $record = DiagnosticEvaluation::withTrashed()->where($criteria)->first();
            if ($record) {
                $record->forceDelete(); // Or just delete() if you want to keep it trashed. 
                // Given the context of "upsert", if they send empty, they probably want it gone.
            }
            // Return a dummy object or handle response? The controller expects a DiagnosticEvaluation.
            // Let's return a new instance or empty model to avoid breaking the signature.
            return new DiagnosticEvaluation(); 
        }

        return $this->repository->updateOrCreate($criteria, $data);
    }

    public function deleteEvaluation(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getDiagnosticReport(int $teacherId, int $courseId, int $classroomId): array
    {
        $currentPeriod = \App\Models\Period::where('is_current', true)->first();
        $periodId = $currentPeriod ? $currentPeriod->id : null;

        $competencies = \App\Models\Competency::where('course_id', $courseId)
            ->where('teacher_id', $teacherId)
            ->get();

        $students = \App\Models\Student::where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('classroom_id', $classroomId)
            ->orderBy('order_number', 'asc')
            ->orderBy('full_name', 'asc')
            ->get();

        $allEvaluations = DiagnosticEvaluation::where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('aula_id', $classroomId)
            ->get()
            ->groupBy('student_id');

        $competenciesReport = $competencies->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'sessions' => [
                    [
                        'id' => $c->id, // Use competency id as identifier for the fake session
                        'session_competency_id' => $c->id,
                        'label' => 'DIAG',
                        'title' => 'Evaluación Diagnóstica',
                        'date' => '' 
                    ]
                ]
            ];
        });

        $reportStudents = $students->map(function ($student) use ($allEvaluations, $competencies) {
            $studentEvals = $allEvaluations->get($student->id, new Collection());
            
            $mappedEvals = $competencies->map(function ($c) use ($studentEvals) {
                $eval = $studentEvals->where('competency_id', $c->id)->first();
                return [
                    'session_competency_id' => $c->id,
                    'evaluations' => $eval ? [['grade' => $eval->grade]] : [],
                    'evidence' => []
                ];
            });

            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'order_number' => $student->order_number,
                'is_exonerated' => $student->is_exonerated,
                'evaluations' => $mappedEvals
            ];
        });

        return [
            'competencies' => $competenciesReport,
            'students' => $reportStudents
        ];
    }

    public function bulkUpsertEvaluations(array $payload, int $teacherId): bool
    {
        $courseId = (int) $payload['course_id'];
        $classroomId = (int) $payload['classroom_id'];
        $periodId = (int) $payload['period_id'];
        $data = $payload['data'];

        $period = \App\Models\Period::find($periodId);
        if (!$period) {
            throw new \InvalidArgumentException("El periodo seleccionado no existe.");
        }

        // 1. Validar que el año académico del periodo pertenezca al docente
        $academicYear = \App\Models\AcademicYear::find($period->academic_year_id);
        if (!$academicYear || (int)$academicYear->teacher_id !== $teacherId) {
            throw new \InvalidArgumentException("El año académico del periodo seleccionado no corresponde al docente.");
        }

        // 2. Validar consistencia: El año académico del aula debe coincidir con el del periodo
        $classroom = \App\Models\Classroom::find($classroomId);
        if ($classroom) {
            if ((int)$classroom->academic_year_id !== (int)$period->academic_year_id) {
                throw new \InvalidArgumentException("El año académico del aula seleccionada no coincide con el año académico del periodo.");
            }
        }

        $academicYearId = (int)$period->academic_year_id;

        foreach ($data as $studentData) {
            $studentId = (int) $studentData['student_id'];
            foreach ($studentData['evaluations'] as $eval) {
                $competencyId = (int) $eval['competency_id'];
                $grade = $eval['grade'];

                $criteria = [
                    'teacher_id' => $teacherId,
                    'academic_year_id' => $academicYearId,
                    'period_id' => $periodId,
                    'student_id' => $studentId,
                    'competency_id' => $competencyId,
                    'course_id' => $courseId,
                    'aula_id' => $classroomId,
                ];

                if ($grade === null || $grade === '') {
                    DiagnosticEvaluation::where($criteria)->delete();
                } else {
                    $this->repository->updateOrCreate($criteria, [
                        'grade' => $grade,
                        'evaluation_date' => date('Y-m-d')
                    ]);
                }
            }
        }

        return true;
    }
}
