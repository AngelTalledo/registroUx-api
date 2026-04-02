<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\AttendanceServiceInterface;
use App\Repositories\AttendanceRepository;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

class AttendanceService implements AttendanceServiceInterface
{
    private AttendanceRepository $repository;

    public function __construct(AttendanceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllAttendancesByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findAllByTeacher($teacherId, $filters);
    }

    public function getAttendanceById(int $id, int $teacherId): ?Attendance
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createAttendance(array $data): Attendance
    {
        return \App\Models\Attendance::updateOrCreate(
            [
                'teacher_id' => $data['teacher_id'],
                'session_id' => $data['session_id'],
                'student_id' => $data['student_id']
            ],
            [
                'status' => $data['status']
            ]
        );
    }

    public function updateAttendance(int $id, int $teacherId, array $data): ?Attendance
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteAttendance(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getAttendanceReport(int $teacherId, int $courseId, int $gradeId, int $classroomId): array
    {
        $course = \App\Models\Course::find($courseId);
        $classroom = \App\Models\Classroom::find($classroomId);

        // Fetch sessions
        $sessions = \App\Models\Session::where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('grade_id', $gradeId)
            ->where('classroom_id', $classroomId)
            ->orderBy('date', 'asc')
            ->get();

        $formattedSessions = $sessions->map(function ($session, $index) {
            return [
                'id' => $session->id,
                'label' => 'S' . ($index + 1),
                'date' => $session->date
            ];
        });

        // Fetch students
        $students = \App\Models\Student::where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->where('grade_id', $gradeId)
            ->where('classroom_id', $classroomId)
            ->orderBy('full_name', 'asc')
            ->get();

        $reportStudents = $students->map(function ($student) use ($sessions) {
            $attendances = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('session_id', $sessions->pluck('id'))
                ->get()
                ->keyBy('session_id');

            $studentAttendance = $sessions->map(function ($session) use ($attendances) {
                return [
                    'session_id' => $session->id,
                    'is_present' => isset($attendances[$session->id]) && $attendances[$session->id]->status === 'PRESENTE'
                ];
            });

            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'attendance' => $studentAttendance
            ];
        });

        return [
            'course' => [
                'id' => $course->id,
                'name' => $course->name
            ],
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->section // Using section as name for classroom
            ],
            'sessions' => $formattedSessions,
            'students' => $reportStudents
        ];
    }

    public function saveBulkAttendance(int $teacherId, int $sessionId, array $attendances): void
    {
        \Illuminate\Database\Capsule\Manager::transaction(function () use ($teacherId, $sessionId, $attendances) {
            foreach ($attendances as $item) {
                \App\Models\Attendance::updateOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'session_id' => $sessionId,
                        'student_id' => $item['student_id']
                    ],
                    [
                        'status' => $item['status']
                    ]
                );
            }
        });
    }
}
