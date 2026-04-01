<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleEntry extends Model
{
    protected $table = 'schedule_entries';

    protected $fillable = [
        'teacher_id',
        'academic_period_id',
        'day_of_week',
        'start_time',
        'end_time',
        'course_id',
        'classroom_id',
        'is_break',
        'color',
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'day_of_week' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class, 'academic_period_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
