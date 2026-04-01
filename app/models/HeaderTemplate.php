<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeaderTemplate extends Model
{
    use SoftDeletes;
    protected $table = 'header_templates';

    protected $fillable = [
        'teacher_id',
        'name',
        'description',
        'type'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
