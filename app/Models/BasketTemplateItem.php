<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasketTemplateItem extends Model
{
    use CrudTrait;

    protected $fillable = [
        'basket_template_id',
        'parish_inventory_item_id',
        'quantity',
    ];

    public function basketTemplate(): BelongsTo
    {
        return $this->belongsTo(BasketTemplate::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(ParishInventoryItem::class, 'parish_inventory_item_id');
    }
}
