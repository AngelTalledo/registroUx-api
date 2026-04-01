<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\AuthServiceInterface;
use Firebase\JWT\JWT;

class AuthService implements AuthServiceInterface
{
    private string $secret;
    private string $algorithm;

    public function __construct(string $secret, string $algorithm)
    {
        $this->secret = $secret;
        $this->algorithm = $algorithm;
    }

    public function generateToken(array $payload): string
    {
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function decodeToken(string $token): ?object
    {
        try {
            // firebase/php-jwt v5.0 uses algorithm as third parameter as string if no Key object is used
            // but the official docs for v5 suggest an array if multiple allowed or just string.
            // Let's use it as provided in the controller previously.
            return JWT::decode($token, $this->secret, [$this->algorithm]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function decodeTokenIgnoringExpiration(string $token): ?object
    {
        try {
            return JWT::decode($token, $this->secret, [$this->algorithm]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')));
                return is_object($payload) ? $payload : null;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function extractTokenFromHeader(string $header): ?string
    {
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
