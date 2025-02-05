<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'type',
        'amount',
        'status',
        'price'
    ];

    // Relación muchos a uno con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación muchos a uno con Skin
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}