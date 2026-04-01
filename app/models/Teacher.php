<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'names',
        'last_names',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function courseGroups()
    {
        return $this->hasMany(CourseGroup::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function competencies()
    {
        return $this->hasMany(Competency::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
