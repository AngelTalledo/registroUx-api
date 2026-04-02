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

    public function getMyCourses(int $teacherId): Collection
    {
        return $this->repository->findMyCourses($teacherId);
    }

    public function bulkStoreStudents(int $teacherId, array $data): array
    {
        // 1. Fetch current maps for resolution (Optimization: fetch once)
        $courses = \App\Models\Course::where('teacher_id', $teacherId)->get()->keyBy('name');
        $grades = \App\Models\Grade::where('teacher_id', $teacherId)->get()->keyBy('name');
        $classrooms = \App\Models\Classroom::where('teacher_id', $teacherId)->get()->keyBy('section');

        $processed = 0;
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($data as $index => $item) {
            try {
                // Resolution (Strictly using English keys: course, grade, classroom)
                $courseName = $item['course'] ?? null;
                $gradeName = $item['grade'] ?? null;
                $sectionName = $item['classroom'] ?? null;

                $courseId = $courseName && isset($courses[$courseName]) ? $courses[$courseName]->id : null;
                $gradeId = $gradeName && isset($grades[$gradeName]) ? $grades[$gradeName]->id : null;
                $classroomId = $sectionName && isset($classrooms[$sectionName]) ? $classrooms[$sectionName]->id : null;

                if (!$courseId || !$gradeId || !$classroomId) {
                    $missing = [];
                    if (!$courseId) $missing[] = "course: " . ($courseName ?? 'n/a');
                    if (!$gradeId) $missing[] = "grade: " . ($gradeName ?? 'n/a');
                    if (!$classroomId) $missing[] = "classroom: " . ($sectionName ?? 'n/a');
                    
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
