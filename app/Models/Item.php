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
        'rarity',
        'price',
        'category',
        'inventory_id',
        'wear',
        'status'
    ];

    // Un item pertenece a un inventario (belongs to)
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
