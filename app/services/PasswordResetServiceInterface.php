<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PasswordResetSession;

interface PasswordResetServiceInterface
{
    /**
     * @param string $email
     * @param string|null $ip
     * @param string|null $userAgent
     * @return string session_token
     */
    public function requestReset(string $email, ?string $ip = null, ?string $userAgent = null): string;

    /**
     * @param string $token
     * @return array ['otp_code' => string, 'expires_at' => string]
     */
    public function getOtpByToken(string $token): array;

    /**
     * @param string $token
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(string $token, string $otp): bool;

    /**
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword): bool;
}
