<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'teacher_id',
        'classroom_id',
        'course_id',
        'grade_id',
        'dni',
        'names',
        'last_names',
        'gender',
        'phone_number',
        'order_number',
        'status',
        'is_exonerated'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_exonerated' => 'boolean'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(StudentStatusHistory::class);
    }
}
