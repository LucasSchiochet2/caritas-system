<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketDeliveryItem extends Model
{
    use CrudTrait;

    protected $fillable = [
        'basket_delivery_id',
        'parish_inventory_item_id',
        'parish_inventory_item_quantity_id',
        'quantity',
    ];

    public function basketDelivery(): BelongsTo
    {
        return $this->belongsTo(BasketDelivery::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(ParishInventoryItem::class, 'parish_inventory_item_id');
    }

    public function inventoryItemQuantity(): BelongsTo
    {
        return $this->belongsTo(ParishInventoryItemQuantity::class, 'parish_inventory_item_quantity_id');
    }
}
