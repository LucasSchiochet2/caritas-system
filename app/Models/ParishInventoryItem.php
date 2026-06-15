<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class ParishInventoryItem extends Model
{
    use CrudTrait;
    protected $fillable = [
        'parish_inventory_id',
        'name',
        'description',
        'total_quantity',
    ];

    public function inventory()
    {
        return $this->belongsTo(ParishInventory::class, 'parish_inventory_id');
    }

    public function quantities()
    {
        return $this->hasMany(ParishInventoryItemQuantity::class);
    }

    public function basketTemplateItems()
    {
        return $this->hasMany(BasketTemplateItem::class);
    }

    public function basketDeliveryItems()
    {
        return $this->hasMany(BasketDeliveryItem::class);
    }
}
