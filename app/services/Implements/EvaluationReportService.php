<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\EvaluationReportServiceInterface;
use App\Models\Student;
use App\Models\Competency;
use App\Models\Evaluation;
use App\Models\Session;
use App\Models\SessionCompetency;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Period;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Teacher;
use Dompdf\Dompdf;
use Dompdf\Options;

class EvaluationReportService implements EvaluationReportServiceInterface
{
    private array $uploadSettings;

    public function __construct(array $uploadSettings)
    {
        $this->uploadSettings = $uploadSettings;
    }

    public function generateEvaluationReport(int $teacherId, array $filters, string $orientation = 'landscape'): string
    {
        // 1. Fetch Metadata
        $academicYear = AcademicYear::find($filters['academic_year_id']);
        $grade = Grade::find($filters['grado_id']);
        $classroom = Classroom::find($filters['aula_id']);
        $course = Course::find($filters['curso_id']);
        $period = Period::find($filters['period_id']);

        $institution = Institution::where('teacher_id', $teacherId)
            ->with(['headerTemplate', 'logos'])
            ->first();

        // 2. Fetch Students
        $students = Student::where('classroom_id', $filters['aula_id'])
            ->where('teacher_id', $teacherId)
            ->where('status', 1)
            ->orderBy('last_names', 'asc')
            ->get();

        // 3. Fetch Competencies for this course
        $competencies = Competency::where('course_id', $filters['curso_id'])
            ->where('teacher_id', $teacherId)
            ->get();

        // 4 & 5. Fetch SessionCompetencies directly using filters
        $sessionCompetencies = SessionCompetency::where('course_id', $filters['curso_id'])
            ->where('classroom_id', $filters['aula_id'])
            ->where('grade_id', $filters['grado_id'])
            ->where('period_id', $filters['period_id'])
            ->where('teacher_id', $teacherId)
            ->orderBy('date', 'asc')
            ->get();
        
        $sessionGroups = $sessionCompetencies->groupBy('competency_id');
        $scIds = $sessionCompetencies->pluck('id')->toArray();

        // 6. Fetch Evaluations
        $evaluations = Evaluation::whereIn('session_competency_id', $scIds)
            ->whereIn('student_id', $students->pluck('id')->toArray())
            ->get();

        // Map evaluations: [student_id][session_competency_id] = grade
        $evaluationMap = [];
        foreach ($evaluations as $evaluation) {
            $evaluationMap[$evaluation->student_id][$evaluation->session_competency_id] = $evaluation->grade;
        }

        $teacher = Teacher::where('user_id', $teacherId)->first();

        // 7. Generate HTML
        $html = $this->renderHtml($academicYear, $grade, $classroom, $course, $period, $students, $competencies, $evaluationMap, $institution, $sessionGroups, $teacher);

        // 8. Convert to PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        // Set chroot to allow access to public directory for images
        $options->set('chroot', realpath($this->uploadSettings['base_path'] . '/..'));
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    private function renderHtml($academicYear, $grade, $classroom, $course, $period, $students, $competencies, $evaluationMap, $institution = null, $sessionGroups = null, $teacher = null): string
    {
        $yearName = $academicYear ? $academicYear->year : 'N/A';
        $gradeName = $grade ? $grade->name : 'N/A';
        $classroomSection = $classroom ? $classroom->section : 'N/A';
        $courseName = $course ? $course->name : 'N/A';
        $periodName = $period ? $period->name : 'N/A';
        $teacherName = $teacher ? $teacher->full_name : 'N/A';

        $html = '
        <html>
        <head>
            <style>
                body { font-family: \'Calibri\', \'Arial\', sans-serif; margin: 0; padding: 0; font-size: 11px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
                thead th { font-family: \'Agency FB\', sans-serif; font-size: 12px; font-weight: bold; }
                th, td { border: 1px solid #000; padding: 4px; text-align: center; }
                .header-wrapper { margin-bottom: 20px; position: relative; }
                .header-top { text-align: center; min-height: 80px; }
                .logo-left { position: absolute; left: 0; top: 0; height: 60px; }
                .logo-right { position: absolute; right: 0; top: 0; height: 60px; }
                .logo-center { display: block; margin: 0 auto 10px auto; height: 60px; }
                .header-content { display: inline-block; vertical-align: top; padding-top: 10px; }
                .inst-title { font-size: 14px; font-weight: bold; margin: 0; }
                .main-report-title { font-size: 18px; font-weight: bold; margin: 5px 0; text-transform: uppercase; }
                
                .meta-table { border: none; margin-top: 10px; }
                .meta-table td { border: none; text-align: left; padding: 2px 5px; font-size: 10px; }
                .meta-label { font-weight: bold; text-transform: uppercase; }

                .student-name { text-align: left; width: 180px; }
                .num-col { width: 20px; }
                .session-col { width: 35px; }
                .bg-gray { background-color: #f2f2f2; }
                .bg-dark-gray { background-color: #d9d9d9; }
            </style>
        </head>
        <body>
            <div class="header-wrapper">
                <div class="header-top">';
        
        $templateType = $institution && $institution->headerTemplate ? (int)$institution->headerTemplate->type : 1;
        $logos = $institution ? $institution->logos : collect([]);
        
        // Render Logos based on template type
        if ($logos->count() > 0) {
            if ($templateType === 3) {
                // Centered Layout
                $logoUrl = realpath($this->uploadSettings['base_path'] . '/logos/' . basename($logos[0]->url));
                if ($logoUrl && file_exists($logoUrl)) {
                    $html .= '<img src="' . $logoUrl . '" class="logo-center">';
                }
            } else {
                // Left or Left+Right Layout
                $logoLeftUrl = realpath($this->uploadSettings['base_path'] . '/logos/' . basename($logos[0]->url));
                if ($logoLeftUrl && file_exists($logoLeftUrl)) {
                    $html .= '<img src="' . $logoLeftUrl . '" class="logo-left">';
                }
                
                // If Type 2 or 4, and we have a second logo
                if (($templateType === 2 || $templateType === 4) && isset($logos[1])) {
                    $logoRightUrl = realpath($this->uploadSettings['base_path'] . '/logos/' . basename($logos[1]->url));
                    if ($logoRightUrl && file_exists($logoRightUrl)) {
                        $html .= '<img src="' . $logoRightUrl . '" class="logo-right">';
                    }
                }
            }
        }

        $instName = $institution ? $institution->name : 'INSTITUCIÓN EDUCATIVA';
        
        $html .= '
                    <div class="header-content">
                        <p class="inst-title">' . $instName . '</p>
                        <h1 class="main-report-title">REGISTRO AUXILIAR DE CALIFICACIONES</h1>
                    </div>
                </div>

                <table class="meta-table">
                    <tr>
                        <td width="35%"><span class="meta-label">DOCENTE:</span> ' . strtoupper($teacherName) . '</td>
                        <td width="35%"><span class="meta-label">ÁREA:</span> ' . strtoupper($courseName) . '</td>
                        <td width="30%"><span class="meta-label">GRADO Y SECCIÓN:</span> ' . strtoupper($gradeName . ' "' . $classroomSection . '"') . '</td>
                    </tr>
                </table>
            </div>

            <table>
                <thead>
                    <tr class="bg-dark-gray">
                        <th rowspan="4" class="num-col">N°</th>
                        <th rowspan="4" style="text-align: center; padding-left: 10px; font-size: 16px; font-family: \'Agency FB\', sans-serif; font-weight: bold;">APELLIDOS Y NOMBRES</th>
                        <th colspan="' . collect($sessionGroups)->sum(fn($g) => count($g) + 1) . '">' . strtoupper($periodName) . '</th>
                    </tr>
                    <tr>';
        
        foreach ($competencies as $competency) {
            $sessionsInComp = isset($sessionGroups[$competency->id]) ? count($sessionGroups[$competency->id]) : 0;
            $colspan = $sessionsInComp + 1; // +1 for average
            $html .= '<th colspan="' . $colspan . '" style="background-color: #e9e9e9; font-size: 9px; text-align: center;">' . strtoupper($competency->name) . '</th>';
        }

        $html .= '      </tr>
                    <tr>';
        
        foreach ($competencies as $competency) {
            $sessionsInComp = isset($sessionGroups[$competency->id]) ? count($sessionGroups[$competency->id]) : 0;
            $colspan = $sessionsInComp + 1;
            $html .= '<th colspan="' . $colspan . '" style="font-weight: normal; font-size: 10px; height: 30px; vertical-align: middle; text-align: center;">' . ($competency->description ?? '') . '</th>';
        }

        $html .= '      </tr>
                    <tr>';
        
        foreach ($competencies as $competency) {
            $sessionsInComp = $sessionGroups[$competency->id] ?? collect();
            foreach ($sessionsInComp as $idx => $s) {
                $html .= '<th class="session-col" title="' . $s->theme . '">SESION - ' . ($idx + 1) . '</th>';
            }
            $html .= '<th class="session-col" style="background-color: #eee;">Prom.</th>';
        }

        $html .= '  </tr>
                </thead>
                <tbody>';

        foreach ($students as $index => $student) {
            $html .= '<tr>
                        <td class="num-col">' . ($index + 1) . '</td>
                        <td class="student-name">' . $student->full_name . '</td>';
            
            $totalPoints = 0;
            $compCount = 0;

            foreach ($competencies as $competency) {
                $sessionsInComp = $sessionGroups[$competency->id] ?? collect();
                $compPoints = 0;
                $compSessionCount = 0;

                foreach ($sessionsInComp as $s) {
                    $gradeLetter = $evaluationMap[$student->id][$s->id] ?? '-';
                    if ($student->is_exonerated) {
                        $gradeLetter = '<span style="color: #666; font-size: 8px;">EXO</span>';
                    }
                    $html .= '<td class="session-col">' . $gradeLetter . '</td>';
                    
                    if (!$student->is_exonerated && $gradeLetter !== '-') {
                        $compPoints += $this->gradeToValue($gradeLetter);
                        $compSessionCount++;
                    }
                }

                $compAvgValue = ($compSessionCount > 0) ? $compPoints / $compSessionCount : null;
                $compAvgGrade = $compAvgValue !== null ? $this->valueToGrade($compAvgValue) : ($student->is_exonerated ? 'EXO' : '-');
                
                $html .= '<td class="session-col" style="background-color: #f9f9f9; font-weight: bold;">' . $compAvgGrade . '</td>';
                
                if ($compAvgValue !== null) {
                    $totalPoints += $compAvgValue;
                    $compCount++;
                }
            }

            $html .= '</tr>';
        }

        $html .= '
                </tbody>
            </table>';

        // --- STATISTICS SECTION ---
        $stats = [];
        foreach ($competencies as $comp) {
            $stats[$comp->id] = [
                'AD' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'total_eval' => 0
            ];
            foreach ($students as $student) {
                if ($student->is_exonerated) continue;
                $sessionsInComp = $sessionGroups[$comp->id] ?? collect();
                $compPoints = 0;
                $compSessionCount = 0;
                foreach ($sessionsInComp as $s) {
                    $gradeLetter = $evaluationMap[$student->id][$s->id] ?? '-';
                    if ($gradeLetter !== '-') {
                        $compPoints += $this->gradeToValue($gradeLetter);
                        $compSessionCount++;
                    }
                }
                if ($compSessionCount > 0) {
                    $avg = $compPoints / $compSessionCount;
                    $grade = $this->valueToGrade($avg);
                    if ($grade !== '-') {
                        $stats[$comp->id][$grade]++;
                        $stats[$comp->id]['total_eval']++;
                    }
                }
            }
        }

        $html .= '
            <div style="margin-top: 30px; page-break-inside: avoid; clear: both;">
                <div style="float: right; width: auto; text-align: right;">
                    <h3 style="margin: 0 0 5px 0; text-transform: uppercase; font-size: 14px;">Estadísticas</h3>
                    <table style="width: auto; border: 1px solid #000; font-size: 10px; margin-left: auto;">
                        <thead>
                            <tr>
                                <td colspan="2" style="border: none;"></td>';
        foreach ($competencies as $comp) {
            $html .= '<th colspan="2" style="font-size: 8px;">' . substr($comp->name, 0, 20) . '</th>';
        }
        $html .= '      </tr>
                        </thead>
                        <tbody>';

        $levels = [
            ['code' => 'C',  'label' => 'INICIO',    'color' => '#ff0000'],
            ['code' => 'B',  'label' => 'PROCESO',   'color' => '#ffff00'],
            ['code' => 'A',  'label' => 'LOGRADO',   'color' => '#92d050'],
            ['code' => 'AD', 'label' => 'DESTACADO', 'color' => '#00b0f0'],
        ];

        foreach ($levels as $level) {
            $html .= '<tr>
                        <td style="background-color: ' . $level['color'] . '; font-weight: bold; width: 80px; text-align: left;">' . $level['label'] . '</td>';
            $html .= '<td style="border: none; width: 10px;"></td>';

            foreach ($competencies as $comp) {
                $count = $stats[$comp->id][$level['code']];
                $total = $stats[$comp->id]['total_eval'];
                $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                
                $html .= '<td style="width: 30px;">' . $count . '</td>';
                $html .= '<td style="width: 40px; background-color: #f2f2f2;">' . $percent . '%</td>';
            }
            $html .= '</tr>';
        }

        $html .= '
                        </tbody>
                    </table>
                    <p style="font-size: 10px; margin-top: 5px;">
                        <strong>Evaluados:</strong> ' . count($students) . '
                    </p>
                </div>
                <div style="clear: both;"></div>
            </div>
        </body>
        </html>';

        return $html;
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
