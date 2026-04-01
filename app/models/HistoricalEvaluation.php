<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalEvaluation extends Model
{
    protected $table = 'historical_evaluations';

    public $timestamps = false; // Using closing_date instead

    protected $fillable = [
        'academic_year_id',
        'period_id',
        'student_id',
        'course_id',
        'classroom_id',
        'competency_id',
        'competency_name',
        'final_grade',
        'is_exonerated',
        'teacher_comment',
        'closing_date'
    ];

    protected $casts = [
        'is_exonerated' => 'boolean',
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

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    public function sessionEvaluations()
    {
        return $this->hasMany(HistoricalSessionEvaluation::class, 'historical_evaluation_id');
    }
}
