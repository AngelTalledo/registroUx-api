<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ScheduleEntry;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Collection;

class ScheduleEntryRepository
{
    public function bulkSave(int $teacherId, int $academicPeriodId, array $entriesData): void
    {
        DB::transaction(function () use ($teacherId, $academicPeriodId, $entriesData) {
            // Cleanup existing entries for this teacher and period
            ScheduleEntry::where('teacher_id', $teacherId)
                ->where('academic_period_id', $academicPeriodId)
                ->delete();

            // Prepare entries for insertion
            $entries = array_map(function ($entry) use ($teacherId, $academicPeriodId) {
                return [
                    'teacher_id' => $teacherId,
                    'academic_period_id' => $academicPeriodId,
                    'day_of_week' => $entry['day_of_week'],
                    'start_time' => $entry['start_time'],
                    'end_time' => $entry['end_time'],
                    'course_id' => $entry['course_id'] ?? null,
                    'classroom_id' => $entry['classroom_id'] ?? null,
                    'is_break' => $entry['is_break'] ?? false,
                    'color' => $entry['color'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }, $entriesData);

            if (!empty($entries)) {
                ScheduleEntry::insert($entries);
            }
        });
    }

    public function findAllByTeacherAndPeriod(int $teacherId, int $academicPeriodId): Collection
    {
        return ScheduleEntry::with(['course', 'classroom'])
            ->where('teacher_id', $teacherId)
            ->where('academic_period_id', $academicPeriodId)
            ->orderBy('day_of_week', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function findByTeacherPeriodAndDay(int $teacherId, int $academicPeriodId, int $dayOfWeek): Collection
    {
        return ScheduleEntry::with(['course', 'classroom'])
            ->where('teacher_id', $teacherId)
            ->where('academic_period_id', $academicPeriodId)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get();
    }
}
