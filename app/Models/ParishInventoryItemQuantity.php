<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class ParishInventoryItemQuantity extends Model
{
    use CrudTrait;
    protected $fillable = [
        'parish_inventory_item_id',
        'quantity',
        'valid_until',
    ];

    public function parishInventoryItem()
    {
        return $this->belongsTo(ParishInventoryItem::class);
    }

    public function basketDeliveryItems()
    {
        return $this->hasMany(BasketDeliveryItem::class);
    }
}
