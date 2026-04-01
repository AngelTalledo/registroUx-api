<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\HeaderTemplate;
use Illuminate\Database\Eloquent\Collection;

class HeaderTemplateRepository
{
    public function findAllByTeacher(int $teacherId): Collection
    {
        return HeaderTemplate::where('teacher_id', $teacherId)->get();
    }

    public function findByIdAndTeacher(int $id, int $teacherId): ?HeaderTemplate
    {
        return HeaderTemplate::where('id', $id)
            ->where('teacher_id', $teacherId)
            ->first();
    }

    public function create(array $data): HeaderTemplate
    {
        return HeaderTemplate::create($data);
    }

    public function update(int $id, int $teacherId, array $data): ?HeaderTemplate
    {
        $template = $this->findByIdAndTeacher($id, $teacherId);
        if ($template) {
            $template->update($data);
            return $template;
        }
        return null;
    }

    public function delete(int $id, int $teacherId): bool
    {
        $template = $this->findByIdAndTeacher($id, $teacherId);
        if ($template) {
            return $template->delete();
        }
        return false;
    }
}
