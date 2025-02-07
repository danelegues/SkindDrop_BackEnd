<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketListing extends Model
{
    protected $table = 'market_listings';

    protected $fillable = [
        'inventory_id',
        'user_id',
        'item_id',
        'price',
        'name',
        'image_url',
        'category',
        'rarity',
        'wear',
        'status'
    ];

    protected $casts = [
        'price' => 'float',
        'user_id' => 'integer',
        'item_id' => 'integer'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}