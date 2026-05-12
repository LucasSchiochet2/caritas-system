<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Database\Factories\BazaarCustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BazaarCustomer extends Model
{
    /** @use HasFactory<BazaarCustomerFactory> */
    use CrudTrait, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'birth_date',
        'cpf',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }
}
