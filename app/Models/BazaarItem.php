<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\BazaarItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BazaarItem extends Model
{
    /** @use HasFactory<BazaarItemFactory> */
    use CrudTrait, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'suggested_price',
        'name',
        'color',
        'size',
        'gender',
        'quantity',
        'condition',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'suggested_price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }
}