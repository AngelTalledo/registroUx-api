<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ScheduleSetting;

class ScheduleSettingRepository
{
    public function findByTeacherAndYear(int $teacherId, int $academicYearId): ?ScheduleSetting
    {
        return ScheduleSetting::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    public function upsert(array $data): ScheduleSetting
    {
        return ScheduleSetting::updateOrCreate(
            [
                'teacher_id' => $data['teacher_id'],
                'academic_year_id' => $data['academic_year_id']
            ],
            [
                'start_time' => $data['start_time'] ?? '08:00:00',
                'end_time' => $data['end_time'] ?? '18:00:00',
                'slot_duration' => $data['slot_duration'] ?? 60
            ]
        );
    }
}
