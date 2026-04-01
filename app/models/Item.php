<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'example_items';

    protected $fillable = [
        'name',
    ];

    public $timestamps = true;
}
