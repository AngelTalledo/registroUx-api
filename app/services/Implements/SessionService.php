<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\SessionServiceInterface;
use App\Repositories\SessionRepository;
use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;

class SessionService implements SessionServiceInterface
{
    private SessionRepository $repository;

    public function __construct(SessionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllSessionsByTeacher(int $teacherId): Collection
    {
        return $this->repository->findAllByTeacher($teacherId);
    }

    public function getSessionById(int $id, int $teacherId): ?Session
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createSession(array $data): Session
    {
        return $this->repository->create($data);
    }

    public function updateSession(int $id, int $teacherId, array $data): ?Session
    {
        return $this->repository->updateByTeacher($id, $teacherId, $data);
    }

    public function deleteSession(int $id, int $teacherId): bool
    {
        return $this->repository->deleteByTeacher($id, $teacherId);
    }

    public function getDeletedSessionsByTeacher(int $teacherId, array $filters = []): Collection
    {
        return $this->repository->findDeletedByTeacher($teacherId, $filters);
    }

    public function restoreSession(int $id, int $teacherId): ?Session
    {
        return $this->repository->restoreByTeacher($id, $teacherId);
    }
}
