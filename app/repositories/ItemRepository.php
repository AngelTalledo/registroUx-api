<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class ItemRepository
{
    public function findAll(): Collection
    {
        return Item::all();
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }
}
