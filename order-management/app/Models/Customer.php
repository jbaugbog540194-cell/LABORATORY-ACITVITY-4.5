<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (self $customer) {
            if ($customer->orders()->exists()) {
                throw new \RuntimeException('Cannot delete a customer with existing orders.');
            }
        });
    }
}
