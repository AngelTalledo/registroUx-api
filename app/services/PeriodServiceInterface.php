<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;

interface PeriodServiceInterface
{
    public function getPeriodsByYear(int $yearId, int $teacherId): Collection;
    public function getPeriodById(int $id, int $teacherId): ?Period;
    public function createPeriod(array $data, int $teacherId): Period;
    public function updatePeriod(int $id, array $data, int $teacherId): ?Period;
    public function deletePeriod(int $id, int $teacherId): bool;
    public function setCurrentPeriod(int $id, int $teacherId): ?Period;
    public function getPeriodsForCurrentYear(int $teacherId): Collection;
}
