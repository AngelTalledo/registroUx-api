<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\AttendanceReportServiceInterface;
use App\Models\Student;
use App\Models\Session;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Period;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Teacher;
use Dompdf\Dompdf;
use Dompdf\Options;

class AttendanceReportService implements AttendanceReportServiceInterface
{
    private array $uploadSettings;

    public function __construct(array $uploadSettings)
    {
        $this->uploadSettings = $uploadSettings;
    }

    public function generateAttendanceReport(int $teacherId, array $filters, string $orientation = 'landscape'): string
    {
        // 1. Fetch Metadata
        $academicYear = AcademicYear::find($filters['academic_year_id']);
        $grade = Grade::find($filters['grado_id']);
        $classroom = Classroom::find($filters['aula_id']);
        $course = Course::find($filters['curso_id']);
        $period = Period::find($filters['period_id']);
        
        // 1.1 Fetch Institution and Logos
        $institution = \App\Models\Institution::where('teacher_id', $teacherId)
            ->with(['headerTemplate', 'logos'])
            ->first();

        // 2. Fetch Students
        $students = Student::where('classroom_id', $filters['aula_id'])
            ->where('teacher_id', $teacherId)
            ->where('status', 1)
            ->orderBy('order_number', 'asc')
            ->orderBy('full_name', 'asc')
            ->get();

        // 3. Fetch Sessions
        $sessions = Session::where('course_id', $filters['curso_id'])
            ->where('classroom_id', $filters['aula_id'])
            ->where('grade_id', $filters['grado_id'])
            ->where('period_id', $filters['period_id'])
            ->orderBy('date', 'asc')
            ->get();

        // 4. Fetch Attendances for these sessions
        $sessionIds = $sessions->pluck('id')->toArray();
        $attendances = Attendance::whereIn('session_id', $sessionIds)->get();

        // Map attendances: [student_id][session_id] = status
        $attendanceMap = [];
        foreach ($attendances as $attendance) {
            $attendanceMap[$attendance->student_id][$attendance->session_id] = $attendance->status;
        }

        $teacher = Teacher::where('user_id', $teacherId)->first();

        // 5. Generate HTML
        $html = $this->renderHtml($academicYear, $grade, $classroom, $course, $period, $students, $sessions, $attendanceMap, $institution, $teacher);

        // 6. Convert to PDF
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

    private function renderHtml($academicYear, $grade, $classroom, $course, $period, $students, $sessions, $attendanceMap, $institution = null, $teacher = null): string
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
                body { font-family: sans-serif; font-size: 9px; margin: 0; padding: 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 5px; }
                th, td { border: 1px solid #000; padding: 2px; text-align: center; }
                
                /* Header Styles */
                .header-wrapper { width: 100%; margin-bottom: 10px; position: relative; }
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

                .student-name { text-align: left; width: 150px; }
                .num-col { width: 25px; }
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
                        <h1 class="main-report-title">REPORTE DE ASISTENCIA DIARIA</h1>
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
                        <th colspan="' . (2 + count($sessions)) . '">' . strtoupper($periodName) . '</th>
                    </tr>
                    <tr>
                        <th rowspan="2" class="num-col">#</th>
                        <th rowspan="2" class="student-name">Estudiante</th>
                        <th colspan="' . count($sessions) . '">Sesiones</th>
                    </tr>
                    <tr>';
        
        foreach ($sessions as $session) {
            $html .= '<th>' . date('d/m', strtotime($session->date)) . '</th>';
        }

        $html .= '  </tr>
                </thead>
                <tbody>';

        foreach ($students as $index => $student) {
            $html .= '<tr>
                        <td>' . ($index + 1) . '</td>
                        <td class="student-name">' . $student->full_name . '</td>';
            
            foreach ($sessions as $session) {
                $status = $attendanceMap[$student->id][$session->id] ?? '-';
                $class = $status !== '-' ? 'status-' . $status : '';
                $html .= '<td class="' . $class . '">' . $status . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        return $html;
    }
}
