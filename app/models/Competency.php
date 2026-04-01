<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competency extends Model
{
    use SoftDeletes;

    protected $table = 'competencies';

    protected $fillable = [
        'teacher_id',
        'academic_year_id',
        'course_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class, 'session_competencies');
    }
}
