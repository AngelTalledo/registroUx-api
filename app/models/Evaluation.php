<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $table = 'evaluations';

    protected $fillable = [
        'teacher_id',
        'session_competency_id',
        'student_id',
        'grade',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function sessionCompetency()
    {
        return $this->belongsTo(SessionCompetency::class, 'session_competency_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function evidences()
    {
        return $this->hasMany(Evidence::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(EvaluationAudit::class);
    }
}
