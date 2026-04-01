<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Collection;

interface AcademicYearServiceInterface
{
    public function getAllAcademicYears(?int $teacherId = null): Collection;
    public function getAcademicYearById(int $id, int $teacherId): ?AcademicYear;
    public function createAcademicYear(array $data): AcademicYear;
    public function updateAcademicYear(int $id, int $teacherId, array $data): ?AcademicYear;
    public function deleteAcademicYear(int $id, int $teacherId): bool;
    public function setCurrentYear(int $id, int $teacherId): ?AcademicYear;
}
