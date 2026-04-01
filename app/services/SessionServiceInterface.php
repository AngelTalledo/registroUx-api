<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;

interface SessionServiceInterface
{
    public function getAllSessionsByTeacher(int $teacherId): Collection;
    public function getSessionById(int $id, int $teacherId): ?Session;
    public function createSession(array $data): Session;
    public function updateSession(int $id, int $teacherId, array $data): ?Session;
    public function deleteSession(int $id, int $teacherId): bool;
    public function getDeletedSessionsByTeacher(int $teacherId, array $filters = []): Collection;
    public function restoreSession(int $id, int $teacherId): ?Session;
}
