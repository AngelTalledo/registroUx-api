<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetSession extends Model
{
    protected $table = 'password_reset_sessions';

    public $timestamps = false; // Using created_at only or manual timestamps

    protected $fillable = [
        'user_id',
        'session_token',
        'otp_code',
        'status',
        'attempts',
        'ip_address',
        'user_agent',
        'expires_at',
        'created_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending/valid sessions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'scanned', 'verified'])
                     ->where('expires_at', '>', date('Y-m-d H:i:s'));
    }
}
