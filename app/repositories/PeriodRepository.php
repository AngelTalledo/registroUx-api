<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;

class PeriodRepository
{
    public function findByAcademicYear(int $yearId): Collection
    {
        return Period::where('academic_year_id', $yearId)
                     ->where('status', 1)
                     ->orderBy('start_date', 'asc')
                     ->get();
    }

    public function findById(int $id): ?Period
    {
        return Period::find($id);
    }

    public function create(array $data): Period
    {
        return Period::create($data);
    }

    public function update(int $id, array $data): ?Period
    {
        $period = $this->findById($id);
        if ($period) {
            $period->update($data);
            return $period;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $period = $this->findById($id);
        if ($period) {
            $period->update(['status' => 0]);
            return $period->delete();
        }
        return false;
    }

    public function markAsCurrent(int $id, int $yearId): ?Period
    {
        // 1. Reset all periods in the same academic year to is_current = 0
        Period::where('academic_year_id', $yearId)
              ->update(['is_current' => 0]);

        // 2. Set target period to is_current = 1
        $period = $this->findById($id);
        if ($period) {
            $period->update(['is_current' => 1]);
        }

        return $period;
    }

    public function findCurrentByAcademicYear(int $yearId): ?Period
    {
        return Period::where('academic_year_id', $yearId)
                     ->where('is_current', 1)
                     ->first();
    }
}
