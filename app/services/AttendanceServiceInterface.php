<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceServiceInterface
{
    public function getAllAttendancesByTeacher(int $teacherId, array $filters = []): Collection;
    public function getAttendanceById(int $id, int $teacherId): ?Attendance;
    public function createAttendance(array $data): Attendance;
    public function updateAttendance(int $id, int $teacherId, array $data): ?Attendance;
    public function deleteAttendance(int $id, int $teacherId): bool;
    public function getAttendanceReport(int $teacherId, int $courseId, int $gradeId, int $classroomId): array;
    public function saveBulkAttendance(int $teacherId, int $sessionId, array $attendances): void;
}
