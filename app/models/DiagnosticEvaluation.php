<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiagnosticEvaluation extends Model
{
    use SoftDeletes;

    protected $table = 'diagnostic_evaluations';

    protected $fillable = [
        'teacher_id',
        'period_id',
        'student_id',
        'competency_id',
        'course_id',
        'aula_id',
        'grade',
        'evaluation_date',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'aula_id');
    }
}
