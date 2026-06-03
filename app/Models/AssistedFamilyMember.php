<?php

namespace App\Models;

use Database\Factories\AssistedFamilyMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistedFamilyMember extends Model
{
    /** @use HasFactory<AssistedFamilyMemberFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parish_id',
        'family_id',
        'name',
        'cpf',
        'birth_date',
        'mother_name',
        'relationship',
        'age',
        'registration_status',
        'registration_date',
        'personal_income',
        'is_responsible',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'birth_date' => 'date:Y-m-d',
            'registration_date' => 'date',
            'personal_income' => 'decimal:2',
            'is_responsible' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }
}
