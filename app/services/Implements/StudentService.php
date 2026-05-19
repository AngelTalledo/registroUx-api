<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\StudentServiceInterface;
use App\Repositories\StudentRepository;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class StudentService implements StudentServiceInterface
{
    private StudentRepository $repository;

    public function __construct(StudentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllStudentsByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getStudentById(int $id, int $teacherId): ?Student
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createStudent(array $data): Student
    {
        return $this->repository->create($data);
    }

    public function updateStudent(int $id, int $teacherId, array $data): ?Student
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteStudent(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getMyCourses(int $teacherId, ?int $academicYearId = null, ?int $academicPeriodId = null): Collection
    {
        return $this->repository->findMyCourses($teacherId, $academicYearId, $academicPeriodId);
    }

    public function bulkStoreStudents(int $teacherId, array $data): array
    {
        // 1. Pre-process maps for O(1) instant lookup (Case-insensitive)
        $coursesMap = \App\Models\Course::where('teacher_id', $teacherId)->get()
            ->keyBy(fn($c) => strtoupper(trim($c->name)));

        $gradesMap = \App\Models\Grade::where('teacher_id', $teacherId)->get()
            ->keyBy(fn($g) => strtoupper(trim($g->name)));

        // Create a unique composite key for classrooms: "GRADENAME|SECTION"
        $classroomsMap = [];
        $allClassrooms = \App\Models\Classroom::with('grade')->where('teacher_id', $teacherId)->get();
        foreach ($allClassrooms as $cl) {
            $gName = strtoupper(trim($cl->grade->name ?? ''));
            $sName = strtoupper(trim($cl->section));
            $classroomsMap["$gName|$sName"] = $cl->id;
        }

        $processed = 0;
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($data as $index => $item) {
            try {
                // Normalize input names
                $courseName = strtoupper(trim($item['course'] ?? ''));
                $gradeName = strtoupper(trim($item['grade'] ?? ''));
                $sectionName = strtoupper(trim($item['classroom'] ?? ''));

                // Direct lookup in maps
                $courseId = $coursesMap[$courseName]->id ?? null;
                $gradeId = $gradesMap[$gradeName]->id ?? null;
                
                // If section was provided as "PRIMERO E", clean it
                $cleanSection = trim(str_ireplace($gradeName, '', $sectionName));
                
                // Try resolving classroom by full name or clean section
                $classroomId = $classroomsMap["$gradeName|$sectionName"] 
                              ?? $classroomsMap["$gradeName|$cleanSection"] 
                              ?? null;

                if (!$courseId || !$gradeId || !$classroomId) {
                    $missing = [];
                    if (!$courseId) $missing[] = "curso: " . ($item['course'] ?? 'n/a');
                    if (!$gradeId) $missing[] = "grado: " . ($item['grade'] ?? 'n/a');
                    if (!$classroomId) $missing[] = "aula: " . ($item['classroom'] ?? 'n/a');
                    
                    $errors[] = "Fila $index: No se pudo resolver " . implode(', ', $missing);
                    continue;
                }

                $studentData = [
                    'teacher_id' => $teacherId,
                    'dni' => $item['dni'] ?? '',
                    'full_name' => $item['full_name'],
                    'gender' => $item['gender'] ?? null,
                    'status' => isset($item['status']) ? (bool)$item['status'] : true,
                    'is_exonerated' => isset($item['is_exonerated']) ? (bool)$item['is_exonerated'] : false,
                    'order_number' => isset($item['order_number']) ? (int)$item['order_number'] : null,
                    'phone_number' => $item['phone_number'] ?? '',
                    'course_id' => $courseId,
                    'grade_id' => $gradeId,
                    'classroom_id' => $classroomId,
                ];

                $student = \App\Models\Student::updateOrCreate(
                    ['id' => $item['id'], 'teacher_id' => $teacherId],
                    $studentData
                );

                if ($student->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
                $processed++;

            } catch (\Exception $e) {
                $errors[] = "Fila $index: " . $e->getMessage();
            }
        }

        return [
            'total' => count($data),
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors
        ];
    }
}
