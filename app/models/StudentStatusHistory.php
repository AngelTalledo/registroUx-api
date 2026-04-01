<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentStatusHistory extends Model
{
    protected $table = 'student_status_history';

    protected $fillable = [
        'student_id',
        'old_status',
        'new_status',
        'reason',
        'changed_by',
        'change_date'
    ];

    protected $casts = [
        'old_status' => 'boolean',
        'new_status' => 'boolean',
        'change_date' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
