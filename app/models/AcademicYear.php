<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use SoftDeletes;

    protected $table = 'academic_years';

    protected $fillable = [
        'teacher_id',
        'year',
        'name',
        'status',
        'is_current',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_current' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function periods()
    {
        return $this->hasMany(Period::class);
    }
}
