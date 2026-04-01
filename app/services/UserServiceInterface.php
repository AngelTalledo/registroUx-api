<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserServiceInterface
{
    public function getAllUsers(): Collection;
    public function registerUser(array $data): User;
    public function getUserById(int $id): ?User;
    public function getUserWithTeacher(int $id): ?User;
    public function authenticateUser(string $email, string $password): ?User;
}
