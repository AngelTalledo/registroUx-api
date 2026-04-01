<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionLogo extends Model
{
    protected $table = 'institution_logos';

    protected $fillable = [
        'institution_id',
        'name',
        'url'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
