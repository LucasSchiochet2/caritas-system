<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashbox extends Model
{
    protected $fillable = [
        'parish_id',
        'name',
        'balance',
    ];

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }
}
