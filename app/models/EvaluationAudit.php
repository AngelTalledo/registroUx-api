<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationAudit extends Model
{
    protected $table = 'evaluation_audit';

    protected $fillable = [
        'evaluation_id',
        'old_grade',
        'new_grade',
        'changed_by'
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
