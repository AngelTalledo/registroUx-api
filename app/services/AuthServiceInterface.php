<?php

declare(strict_types=1);

namespace App\Services;

interface AuthServiceInterface
{
    public function generateToken(array $payload): string;
    public function decodeToken(string $token): ?object;
    public function decodeTokenIgnoringExpiration(string $token): ?object;
    public function extractTokenFromHeader(string $header): ?string;
}
