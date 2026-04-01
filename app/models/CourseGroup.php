<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseGroup extends Model
{
    use SoftDeletes;

    protected $table = 'course_groups';

    protected $fillable = [
        'teacher_id',
        'name',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
