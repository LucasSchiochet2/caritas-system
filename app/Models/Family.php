<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Family extends Model
{
    /** @use HasFactory<FamilyFactory> */
    use CrudTrait, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parish_id',
        'name',
        'address',
        'observations',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function assistedFamilyMembers(): HasMany
    {
        return $this->hasMany(AssistedFamilyMember::class);
    }

    public function responsible(): HasOne
    {
        return $this->hasOne(AssistedFamilyMember::class)->where('is_responsible', true);
    }

    public function basketDeliveries(): HasMany
    {
        return $this->hasMany(BasketDelivery::class);
    }
}
