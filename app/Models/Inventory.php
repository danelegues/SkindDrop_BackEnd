<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'user_id',
        'status'
    ];

    // Un inventario tiene muchos items (one-to-many)
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function marketListings()
    {
        return $this->hasMany(MarketListing::class);
    }
}