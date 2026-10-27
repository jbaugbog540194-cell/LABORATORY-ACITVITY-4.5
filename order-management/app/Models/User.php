<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_staff',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_staff' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $user) {
            if ($user->orders()->exists()) {
                throw new \RuntimeException('Cannot delete staff member with existing orders.');
            }
        });
    }
}
