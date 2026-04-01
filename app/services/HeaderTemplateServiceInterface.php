<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HeaderTemplate;
use Illuminate\Database\Eloquent\Collection;

interface HeaderTemplateServiceInterface
{
    public function getAllTemplates(int $teacherId): Collection;
    public function getTemplateById(int $id, int $teacherId): ?HeaderTemplate;
    public function createTemplate(array $data): HeaderTemplate;
    public function updateTemplate(int $id, int $teacherId, array $data): ?HeaderTemplate;
    public function deleteTemplate(int $id, int $teacherId): bool;
}
