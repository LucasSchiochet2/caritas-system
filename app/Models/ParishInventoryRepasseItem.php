<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParishInventoryRepasseItem extends Model
{
    protected $fillable = [
        'parish_inventory_repasse_id',
        'name',
        'description',
        'quantity',
        'unit',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function repasse(): BelongsTo
    {
        return $this->belongsTo(ParishInventoryRepasse::class, 'parish_inventory_repasse_id');
    }
}
