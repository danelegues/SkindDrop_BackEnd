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
        'inventory_id'
    ];

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
}
