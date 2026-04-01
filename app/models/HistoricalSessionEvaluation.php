<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalSessionEvaluation extends Model
{
    protected $table = 'historical_session_evaluations';

    public $timestamps = false;

    protected $fillable = [
        'historical_evaluation_id',
        'session_competency_id',
        'grade',
        'session_label',
        'session_date',
        'session_theme'
    ];

    protected $casts = [
        'session_date' => 'date'
    ];

    public function historicalEvaluation()
    {
        return $this->belongsTo(HistoricalEvaluation::class, 'historical_evaluation_id');
    }

    public function sessionCompetency()
    {
        // session_competency_id points to the evaluation-specific session table
        return $this->belongsTo(SessionCompetency::class, 'session_competency_id');
    }
}
