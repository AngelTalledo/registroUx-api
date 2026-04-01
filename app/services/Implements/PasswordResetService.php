<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\PasswordResetServiceInterface;
use App\Models\PasswordResetSession;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;

class PasswordResetService implements PasswordResetServiceInterface
{
    private int $expirationMinutes = 15;
    private int $maxAttempts = 3;

    public function requestReset(string $email, ?string $ip = null, ?string $userAgent = null): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception("Usuario no encontrado");
        }

        // Invalidate previous active sessions
        PasswordResetSession::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'scanned', 'verified'])
            ->update(['status' => 'expired']);

        $token = bin2hex(random_bytes(32));
        $otp = (string)random_int(100000, 999999);

        PasswordResetSession::create([
            'user_id' => $user->id,
            'session_token' => $token,
            'otp_code' => $otp, // Plain text for simulation as per plan
            'status' => 'pending',
            'attempts' => 0,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$this->expirationMinutes} minutes"))
        ]);

        return $token;
    }

    public function getOtpByToken(string $token): array
    {
        $session = PasswordResetSession::where('session_token', $token)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$session) {
            throw new Exception("Sesión inválida o expirada");
        }

        $expiresAt = strtotime($session->expires_at);
        $remainingSeconds = $expiresAt - time();

        return [
            'otp_code' => $session->otp_code,
            'expires_at' => $session->expires_at,
            'remaining_seconds' => max(0, $remainingSeconds)
        ];
    }

    public function verifyOtp(string $token, string $otp): bool
    {
        $session = PasswordResetSession::where('session_token', $token)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$session) {
            throw new Exception("Sesión inválida o expirada");
        }

        if ($session->status === 'used' || $session->status === 'expired') {
            throw new Exception("Esta sesión ya no es válida");
        }

        if ($session->attempts >= $this->maxAttempts) {
            $session->update(['status' => 'expired']);
            throw new Exception("Máximo de intentos alcanzado. Inicie el proceso nuevamente.");
        }

        if ($session->otp_code === $otp) {
            $session->update([
                'status' => 'verified',
                'attempts' => $session->attempts + 1
            ]);
            return true;
        }

        $session->increment('attempts');
        return false;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $session = PasswordResetSession::where('session_token', $token)
            ->where('status', 'verified')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$session) {
            throw new Exception("Paso de verificación no completado o sesión expirada");
        }

        return DB::transaction(function () use ($session, $newPassword) {
            $user = User::find($session->user_id);
            if (!$user) throw new Exception("Usuario no encontrado");

            // Update user password
            $user->update([
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);

            // Finalize session
            $session->update(['status' => 'used']);

            // Invalidate any other active sessions
            PasswordResetSession::where('user_id', $user->id)
                ->where('id', '!=', $session->id)
                ->whereIn('status', ['pending', 'scanned', 'verified'])
                ->update(['status' => 'expired']);

            return true;
        });
    }
}
