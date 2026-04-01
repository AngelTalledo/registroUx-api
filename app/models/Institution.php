<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $table = 'institutions';

    protected $fillable = [
        'teacher_id',
        'name',
        'header_template_id',
        'report_enabled'
    ];

    protected $casts = [
        'report_enabled' => 'boolean'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function logos()
    {
        return $this->hasMany(InstitutionLogo::class);
    }

    public function headerTemplate()
    {
        return $this->belongsTo(HeaderTemplate::class, 'header_template_id');
    }
}
