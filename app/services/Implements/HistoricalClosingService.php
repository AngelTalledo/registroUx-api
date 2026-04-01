<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\HistoricalClosingServiceInterface;
use App\Models\HistoricalEvaluation;
use App\Models\HistoricalSessionEvaluation;
use App\Models\HistoricalAttendance;
use App\Models\Student;
use App\Models\Competency;
use App\Models\SessionCompetency;
use App\Models\Evaluation;
use App\Models\Session;
use App\Models\Attendance;
use Illuminate\Database\Capsule\Manager as DB;

class HistoricalClosingService implements HistoricalClosingServiceInterface
{
    public function __construct()
    {
    }

    public function closePeriod(int $teacherId, array $data): array
    {
        $type = $data['type'] ?? 'all';
        $results = [];

        if ($type === 'evaluation' || $type === 'all') {
            $results['evaluation'] = $this->processEvaluationClosing($teacherId, $data);
        }

        if ($type === 'attendance' || $type === 'all') {
            $results['attendance'] = $this->processAttendanceClosing($teacherId, $data);
        }

        return $results;
    }

    private function processEvaluationClosing(int $teacherId, array $filters): array
    {
        return DB::transaction(function () use ($teacherId, $filters) {
            // 1. Clear existing historical evaluations for this period/course/classroom
            $deletedCount = HistoricalEvaluation::where('period_id', $filters['period_id'])
                ->where('course_id', $filters['course_id'])
                ->where('classroom_id', $filters['aula_id'])
                ->delete();

            // 2. Fetch Students
            $students = Student::where('classroom_id', $filters['aula_id'])
                ->where('teacher_id', $teacherId)
                ->where('status', 1)
                ->get();

            // 3. Fetch Competencies
            $competencies = Competency::where('course_id', $filters['course_id'])
                ->get();

            // 4. Fetch SessionCompetencies
            $sessionCompetencies = SessionCompetency::where('course_id', $filters['course_id'])
                ->where('classroom_id', $filters['aula_id'])
                ->where('period_id', $filters['period_id'])
                ->get();

            $sessionGroups = $sessionCompetencies->groupBy('competency_id');
            $scIds = $sessionCompetencies->pluck('id')->toArray();

            // 5. Fetch Evaluations
            $evaluations = Evaluation::whereIn('session_competency_id', $scIds)
                ->get()
                ->groupBy('student_id');

            $processedCount = 0;

            foreach ($students as $student) {
                foreach ($competencies as $competency) {
                    $sessionsInComp = $sessionGroups[$competency->id] ?? collect();
                    $studentEvals = $evaluations[$student->id] ?? collect();
                    
                    $compPoints = 0;
                    $compSessionCount = 0;
                    $sessionDetails = [];

                    foreach ($sessionsInComp as $s) {
                        $eval = $studentEvals->firstWhere('session_competency_id', $s->id);
                        $gradeLetter = $eval ? $eval->grade : '-';
                        
                        if ($gradeLetter !== '-') {
                            $compPoints += $this->gradeToValue($gradeLetter);
                            $compSessionCount++;
                        }

                        $sessionDetails[] = [
                            'session_competency_id' => $s->id,
                            'grade' => $gradeLetter,
                            'session_label' => $s->type, // Label/Name
                            'session_date' => $s->date,
                            'session_theme' => $s->theme
                        ];
                    }

                    $compAvgValue = ($compSessionCount > 0) ? $compPoints / $compSessionCount : null;
                    $finalGrade = $compAvgValue !== null ? $this->valueToGrade($compAvgValue) : ($student->is_exonerated ? 'EXO' : '-');

                    // Create Historical Evaluation
                    $histEval = HistoricalEvaluation::create([
                        'academic_year_id' => $filters['academic_year_id'],
                        'period_id' => $filters['period_id'],
                        'student_id' => $student->id,
                        'course_id' => $filters['course_id'],
                        'classroom_id' => $filters['aula_id'],
                        'competency_id' => $competency->id,
                        'competency_name' => $competency->name,
                        'final_grade' => $finalGrade,
                        'is_exonerated' => (bool)$student->is_exonerated,
                        'closing_date' => date('Y-m-d H:i:s')
                    ]);

                    // Create Session Details
                    foreach ($sessionDetails as $detail) {
                        HistoricalSessionEvaluation::create(array_merge($detail, [
                            'historical_evaluation_id' => $histEval->id
                        ]));
                    }

                    $processedCount++;
                }
            }

            return ['processed' => $processedCount, 'overwritten' => $deletedCount];
        });
    }

    private function processAttendanceClosing(int $teacherId, array $filters): array
    {
        return DB::transaction(function () use ($teacherId, $filters) {
            // 1. Clear existing
            $deletedCount = HistoricalAttendance::where('period_id', $filters['period_id'])
                ->where('course_id', $filters['course_id'])
                ->delete();

            // 2. Fetch Sessions for the period and course group
            // We need to find sessions linked to the course
            $sessions = Session::where('period_id', $filters['period_id'])
                ->where('teacher_id', $teacherId)
                ->get();

            // 3. Fetch Attendances
            $attendances = Attendance::whereIn('session_id', $sessions->pluck('id')->toArray())
                ->get()
                ->groupBy('student_id');

            // 4. Fetch Students
            $students = Student::where('classroom_id', $filters['aula_id'])
                ->get();

            $processedCount = 0;

            foreach ($students as $student) {
                $studentAtt = $attendances[$student->id] ?? collect();
                
                $presents = $studentAtt->where('status', 'PRESENTE')->count();
                $absents = $studentAtt->where('status', 'FALTA')->count();
                $tardies = $studentAtt->where('status', 'TARDANZA')->count();
                $justified = 0; // The enum didn't have justified, but historical table does. 

                HistoricalAttendance::create([
                    'academic_year_id' => $filters['academic_year_id'],
                    'period_id' => $filters['period_id'],
                    'student_id' => $student->id,
                    'course_id' => $filters['course_id'],
                    'total_sessions' => $sessions->count(),
                    'total_presents' => $presents,
                    'total_absents' => $absents,
                    'total_tardies' => $tardies,
                    'total_justified' => $justified,
                    'closing_date' => date('Y-m-d H:i:s')
                ]);

                $processedCount++;
            }

            return ['processed' => $processedCount, 'overwritten' => $deletedCount];
        });
    }

    private function gradeToValue(string $grade): int
    {
        return match (strtoupper(trim($grade))) {
            'AD' => 4,
            'A' => 3,
            'B' => 2,
            'C' => 1,
            default => 0,
        };
    }

    private function valueToGrade(float $value): string
    {
        if ($value >= 3.5) return 'AD';
        if ($value >= 2.5) return 'A';
        if ($value >= 1.5) return 'B';
        if ($value > 0) return 'C';
        return '-';
    }
}
