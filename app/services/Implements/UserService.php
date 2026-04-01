<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\UserServiceInterface;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\PasswordResetSession;

class UserService implements UserServiceInterface
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllUsers(): Collection
    {
        return $this->repository->findAll();
    }

    public function registerUser(array $data): User
    {
        // El hash de la contraseña debe hacerse aquí o en el modelo
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->repository->create($data);
    }

    public function getUserById(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function getUserWithTeacher(int $id): ?User
    {
        return User::with('teacher')->find($id);
    }

    public function authenticateUser(string $email, string $password): ?User
    {
        $user = $this->repository->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            // Invalidate any active password reset sessions
            PasswordResetSession::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'scanned', 'verified'])
                ->update(['status' => 'expired']);

            return $user;
        }

        return null; // Autenticación fallida
    }
}
