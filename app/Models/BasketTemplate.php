<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BasketTemplate extends Model
{
    use CrudTrait;

    protected $fillable = [
        'parish_id',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BasketTemplateItem::class);
    }
}
