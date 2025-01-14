<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'balance'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Event Listener para validar balance antes de guardar
    public static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->balance < 0) {
                // Lanzar una excepción genérica con un mensaje de error
                throw new \Exception('El balance no puede ser menor a 0.');
            }
        });
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
