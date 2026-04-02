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

    public function upsertEvaluation(array $data): DiagnosticEvaluation
    {
        $criteria = [
            'teacher_id' => $data['teacher_id'],
            'period_id' => $data['period_id'],
            'student_id' => $data['student_id'],
            'competency_id' => $data['competency_id'],
            'course_id' => $data['course_id'],
            'aula_id' => $data['aula_id'],
        ];

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
                'is_exonerated' => $student->is_exonerated,
                'evaluations' => $mappedEvals
            ];
        });

        return [
            'competencies' => $competenciesReport,
            'students' => $reportStudents
        ];
    }
}
