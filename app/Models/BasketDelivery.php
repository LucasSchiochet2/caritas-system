<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BasketDelivery extends Model
{
    use CrudTrait;

    protected $fillable = [
        'parish_id',
        'family_id',
        'basket_template_id',
        'created_by',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BasketTemplate::class, 'basket_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BasketDeliveryItem::class);
    }
}
