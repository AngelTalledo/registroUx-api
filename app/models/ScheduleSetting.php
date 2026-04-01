<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleSetting extends Model
{
    protected $table = 'schedule_settings';

    protected $fillable = [
        'teacher_id',
        'academic_year_id',
        'start_time',
        'end_time',
        'slot_duration',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
