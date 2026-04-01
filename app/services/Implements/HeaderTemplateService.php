<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Models\HeaderTemplate;
use App\Repositories\HeaderTemplateRepository;
use App\Services\HeaderTemplateServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class HeaderTemplateService implements HeaderTemplateServiceInterface
{
    private HeaderTemplateRepository $repository;

    public function __construct(HeaderTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTemplates(int $teacherId): Collection
    {
        return $this->repository->findAllByTeacher($teacherId);
    }

    public function getTemplateById(int $id, int $teacherId): ?HeaderTemplate
    {
        return $this->repository->findByIdAndTeacher($id, $teacherId);
    }

    public function createTemplate(array $data): HeaderTemplate
    {
        return $this->repository->create($data);
    }

    public function updateTemplate(int $id, int $teacherId, array $data): ?HeaderTemplate
    {
        return $this->repository->update($id, $teacherId, $data);
    }

    public function deleteTemplate(int $id, int $teacherId): bool
    {
        return $this->repository->delete($id, $teacherId);
    }
}
