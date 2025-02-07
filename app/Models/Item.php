<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'price',
        'rarity',
        'category',
        'wear',
        'status',
        'inventory_id',
        'is_skindrop_market',
        'available'
    ];

    protected $casts = [
        'price' => 'float',
        'is_skindrop_market' => 'boolean',
        'available' => 'boolean'
    ];

    // Definir las posibles opciones de status
    const STATUS_TEMPLATE = 'template';
    const STATUS_AVAILABLE = 'available';
    const STATUS_ON_SALE = 'on_sale';
    const STATUS_LOCKED = 'locked';

    // Un item pertenece a un inventario (belongs to)
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    //pruebaCarlos
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function marketListing()
    {
        return $this->hasOne(MarketListing::class);
    }
}
