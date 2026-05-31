<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class LogsCashbox extends Model
{
    use CrudTrait;
    protected $fillable = [
        'cashbox_id',
        'user_id',
        'movement_type',
        'reason',
        'amount',
    ];

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
