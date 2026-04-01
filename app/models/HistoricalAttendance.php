<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalAttendance extends Model
{
    protected $table = 'historical_attendance';

    public $timestamps = false;

    protected $fillable = [
        'academic_year_id',
        'period_id',
        'student_id',
        'course_id',
        'total_sessions',
        'total_presents',
        'total_absents',
        'total_tardies',
        'total_justified',
        'closing_date'
    ];

    protected $casts = [
        'closing_date' => 'datetime'
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
