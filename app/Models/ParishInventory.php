<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParishInventory extends Model
{
    protected $fillable = [
        'parish_id',
        'name',
        'description',
    ];

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }
}
