<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image_url', 'price'];

    // Relación con items
    public function items()
    {
        return $this->belongsToMany(Item::class, 'crate_item');
    }
} 