<?php

declare(strict_types=1);

namespace App\Services\Implements;

use App\Services\ItemServiceInterface;
use App\Repositories\ItemRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Item;

class ItemService implements ItemServiceInterface
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllItems(): Collection
    {
        return $this->repository->findAll();
    }

    public function createItem(array $data): Item
    {
        return $this->repository->create($data);
    }
}
