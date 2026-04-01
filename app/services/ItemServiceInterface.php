<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

interface ItemServiceInterface
{
    public function getAllItems(): Collection;
    public function createItem(array $data): Item;
}
