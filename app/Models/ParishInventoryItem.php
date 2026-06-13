<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParishInventoryItem extends Model
{
    final protected $fillable = [
        'parish_inventory_id',
        'name',
        'description',
        'total_quantity',
    ];

    public function inventory()
    {
        return $this->belongsTo(ParishInventory::class, 'parish_inventory_id');
    }
}
