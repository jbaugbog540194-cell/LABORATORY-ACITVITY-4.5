<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_reference',
        'payment_method',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function recordForOrder(int $orderId, array $data): self
    {
        return DB::transaction(function () use ($orderId, $data) {
            $order = Order::query()->with('payments')->findOrFail($orderId);
            $newAmount = (float) ($data['amount'] ?? 0);
            $currentTotal = (float) $order->payments()->sum('amount');

            if ($newAmount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Payment amount must be greater than zero.'],
                ]);
            }

            if (($currentTotal + $newAmount) > (float) $order->total_amount) {
                throw ValidationException::withMessages([
                    'amount' => ['The total of all payments cannot exceed the order total.'],
                ]);
            }

            return static::query()->create([
                'order_id' => $orderId,
                'payment_reference' => $data['payment_reference'] ?? 'PAY-' . now()->format('YmdHis'),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'amount' => $newAmount,
                'paid_at' => $data['paid_at'] ?? now(),
            ]);
        });
    }
}
