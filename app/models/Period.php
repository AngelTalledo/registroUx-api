<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes;

    protected $table = 'periods';

    protected $fillable = [
        'academic_year_id',
        'name',
        'is_current',
        'start_date',
        'end_date',
        'status'
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
