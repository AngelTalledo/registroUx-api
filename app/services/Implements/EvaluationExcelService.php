<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\EvaluationExcelServiceInterface;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EvaluationExcelService implements EvaluationExcelServiceInterface
{
    private array $uploadSettings;

    public function __construct(array $uploadSettings)
    {
        $this->uploadSettings = $uploadSettings;
    }

    public function generateEvaluationExcel(int $teacherId, array $filters): string
    {
        // 1. Fetch Metadata
        $academicYear = AcademicYear::find($filters['academic_year_id']);
        $grade = Grade::find($filters['grado_id']);
        $classroom = Classroom::find($filters['aula_id']);
        $course = Course::find($filters['curso_id']);
        $period = Period::find($filters['period_id']);
        $teacher = Teacher::where('user_id', $teacherId)->first();
        $institution = Institution::where('teacher_id', $teacherId)
            ->with(['headerTemplate', 'logos'])
            ->first();

        // 2. Fetch Students
        $students = Student::where('classroom_id', $filters['aula_id'])
            ->where('teacher_id', $teacherId)
            ->where('status', 1)
            ->orderBy('order_number', 'asc')
            ->orderBy('full_name', 'asc')
            ->get();

        // 3. Fetch Competencies
        $competencies = Competency::where('course_id', $filters['curso_id'])->get();

        // 4. Fetch SessionCompetencies
        $sessionCompetencies = SessionCompetency::where('course_id', $filters['curso_id'])
            ->where('classroom_id', $filters['aula_id'])
            ->where('grade_id', $filters['grado_id'])
            ->where('period_id', $filters['period_id'])
            ->where('teacher_id', $teacherId)
            ->orderBy('date', 'asc')
            ->get();
        
        $sessionGroups = $sessionCompetencies->groupBy('competency_id');
        $scIds = $sessionCompetencies->pluck('id')->toArray();

        // 5. Fetch Evaluations
        $evaluations = Evaluation::whereIn('session_competency_id', $scIds)
            ->whereIn('student_id', $students->pluck('id')->toArray())
            ->get();

        $evaluationMap = [];
        foreach ($evaluations as $evaluation) {
            $evaluationMap[$evaluation->student_id][$evaluation->session_competency_id] = $evaluation->grade;
        }

        // 6. Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Registro Auxiliar');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12, 'name' => 'Agency FB'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']]
        ];

        $metaLabelStyle = ['font' => ['bold' => true, 'name' => 'Agency FB']];

        // Global font setting (Default for data rows)
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        // Header Section with Logos
        $templateType = $institution && $institution->headerTemplate ? (int)$institution->headerTemplate->type : 1;
        $logos = $institution ? $institution->logos : collect([]);

        if ($logos->count() > 0) {
            if ($templateType === 3) {
                // Centered Layout
                $this->addLogoToSheet($sheet, $logos[0], 'F', 1, 60);
            } else {
                // Left Logo (Common for 1, 2, 4)
                $this->addLogoToSheet($sheet, $logos[0], 'A', 1, 60);
                
                // Right Logo (Type 2 or 4)
                if (($templateType === 2 || $templateType === 4) && isset($logos[1])) {
                    // Calculate right column based on total columns
                    $totalSessions = count($sessionCompetencies);
                    $totalColsCount = 2 + $totalSessions + count($competencies);
                    $rightColLetter = $this->getColLetter($totalColsCount);
                    $this->addLogoToSheet($sheet, $logos[1], $rightColLetter, 1, 60, 'right');
                }
            }
        }

        $sheet->setCellValue('C1', $institution ? strtoupper($institution->name) : 'INSTITUCIÓN EDUCATIVA');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('C1:K1');
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('C2', 'REGISTRO AUXILIAR DE CALIFICACIONES');
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('C2:K2');
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata Info
        $row = 4;
        $sheet->setCellValue('A'.$row, 'DOCENTE:');
        $sheet->setCellValue('B'.$row, strtoupper($teacher ? $teacher->full_name : 'N/A'));
        $sheet->setCellValue('E'.$row, 'ÁREA:');
        $sheet->setCellValue('F'.$row, strtoupper($course ? $course->name : 'N/A'));
        $sheet->setCellValue('I'.$row, 'GRADO Y SECCIÓN:');
        $sheet->setCellValue('J'.$row, strtoupper(($grade ? $grade->name : 'N/A') . ' "' . ($classroom ? $classroom->section : 'N/A') . '"'));
        $sheet->getStyle("A$row:I$row")->applyFromArray($metaLabelStyle);

        // Table Header
        $row = 6;
        $totalSessions = count($sessionCompetencies);
        $totalCols = 2 + $totalSessions + count($competencies); // # + Name + sessions + comp averages
        
        $sheet->setCellValue('A'.$row, 'N°');
        $sheet->setCellValue('B'.$row, 'APELLIDOS Y NOMBRES');
        $sheet->mergeCells('A'.$row.':A'.($row+3)); // Merge from Period row down to Sessions row (4 rows)
        $sheet->mergeCells('B'.$row.':B'.($row+3));

        $sheet->setCellValue('C'.$row, strtoupper($period ? $period->name : 'PERIODO'));
        $sheet->mergeCells($this->getColLetter(3).$row.':'.$this->getColLetter($totalCols).$row);
        $sheet->getStyle($this->getColLetter(3).$row)->applyFromArray($headerStyle);
        $sheet->getStyle($this->getColLetter(3).$row)->getFont()->setSize(16);

        // Center N° and Name vertically/horizontally
        $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray($headerStyle);

        $row++; // Move to Row 7 (Competency Name)
        
        $col = 3;
        foreach ($competencies as $comp) {
            $sessionsInComp = isset($sessionGroups[$comp->id]) ? count($sessionGroups[$comp->id]) : 0;
            $colspan = $sessionsInComp + 1;
            
            $startCol = $this->getColLetter($col);
            $endCol = $this->getColLetter($col + $colspan - 1);
            
            // Row 7: Competency Name
            $sheet->setCellValue($startCol.$row, strtoupper($comp->name));
            $sheet->mergeCells($startCol.$row.':'.$endCol.$row);
            $sheet->getStyle($startCol.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($startCol.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9E9E9');
            $sheet->getStyle($startCol.$row)->getFont()->setBold(true);
            
            // Row 8: Competency Description
            $descRow = $row + 1;
            $sheet->setCellValue($startCol.$descRow, $comp->description ?? '');
            $sheet->mergeCells($startCol.$descRow.':'.$endCol.$descRow);
            $sheet->getStyle($startCol.$descRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($startCol.$descRow)->getFont()->setBold(false)->setSize(10);
            
            // Row 9: Sub-header (Sessions + PROM)
            $subRow = $row + 2;
            $subCol = $col;
            if (isset($sessionGroups[$comp->id])) {
                foreach ($sessionGroups[$comp->id] as $sIndex => $sComp) {
                    $sheet->setCellValue($this->getColLetter($subCol).$subRow, 'S' . ($sIndex + 1));
                    $subCol++;
                }
            }
            $sheet->setCellValue($this->getColLetter($subCol).$subRow, 'PROM');
            
            $col += $colspan;
        }
        
        $sheet->getStyle('A'.($row-1).':'.$this->getColLetter($totalCols).($row+2))->applyFromArray($headerStyle);

        // Data Row
        $dataRow = $row + 3;
        foreach ($students as $index => $student) {
            $sheet->setCellValue('A'.$dataRow, $index + 1);
            $sheet->setCellValue('B'.$dataRow, $student->full_name);
            
            // Apply borders to # and Student Name
            $sheet->getStyle('A'.$dataRow.':B'.$dataRow)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            $col = 3;
            foreach ($competencies as $comp) {
                $compPoints = 0;
                $compSessionCount = 0;
                $sessionsInComp = $sessionGroups[$comp->id] ?? [];

                foreach ($sessionsInComp as $sComp) {
                    $grade = $evaluationMap[$student->id][$sComp->id] ?? '-';
                    if ($student->is_exonerated) $grade = 'EXO';
                    
                    $cell = $this->getColLetter($col).$dataRow;
                    $sheet->setCellValue($cell, $grade);
                    $this->applyGradeStyle($sheet, $cell, $grade);
                    
                    if (!$student->is_exonerated && $grade !== '-') {
                        $compPoints += $this->gradeToValue($grade);
                        $compSessionCount++;
                    }
                    $col++;
                }
                
                // Comp Average
                $avgValue = ($compSessionCount > 0) ? $compPoints / $compSessionCount : null;
                $avgGrade = $avgValue !== null ? $this->valueToGrade($avgValue) : ($student->is_exonerated ? 'EXO' : '-');
                
                $cell = $this->getColLetter($col).$dataRow;
                $sheet->setCellValue($cell, $avgGrade);
                $this->applyGradeStyle($sheet, $cell, $avgGrade);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $col++;
            }
            $dataRow++;
        }

        // Auto-size columns
        foreach (range('A', $this->getColLetter($totalCols)) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Output
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    private function getColLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $remainder = ($col - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $col = (int)(($col - $remainder) / 26);
        }
        return $letter;
    }

    private function gradeToValue(string $grade): int
    {
        return match ($grade) {
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
        return 'C';
    }

    private function applyGradeStyle($sheet, $cell, $grade): void
    {
        $style = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        
        $color = match ($grade) {
            'AD' => 'BDD7EE', // Blue
            'A' => 'C6E0B4',  // Green
            'B' => 'FFE699',  // Yellow
            'C' => 'F8CBAD',  // Red/Orange
            'EXO' => 'D9D9D9', // Gray
            default => null
        };

        if ($color) {
            $style['fill'] = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color]
            ];
        }
        
        $sheet->getStyle($cell)->applyFromArray($style);
    }

    private function addLogoToSheet($sheet, $logo, $col, $row, $height, $anchor = 'left'): void
    {
        $path = realpath($this->uploadSettings['base_path'] . '/logos/' . basename($logo->url));
        if ($path && file_exists($path)) {
            $drawing = new Drawing();
            $drawing->setName($logo->name ?? 'Logo');
            $drawing->setPath($path);
            $drawing->setHeight($height);
            $drawing->setCoordinates($col . $row);
            
            if ($anchor === 'right') {
                $drawing->setOffsetX(-20); // Subtle shift from the edge
            }
            
            $drawing->setWorksheet($sheet);
        }
    }
}
