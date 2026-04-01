<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evidence extends Model
{
    use SoftDeletes;

    protected $table = 'evidences';

    protected $fillable = [
        'evaluation_id',
        'file_url',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}
