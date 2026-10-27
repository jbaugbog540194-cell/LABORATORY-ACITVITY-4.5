<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'order_date',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function createWithItems(int $customerId, int $staffId, array $items, string $status = 'pending', ?string $orderNumber = null): self
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['An order must contain at least one product.'],
            ]);
        }

        return DB::transaction(function () use ($customerId, $staffId, $items, $status, $orderNumber) {
            $order = static::query()->create([
                'order_number' => $orderNumber ?? static::generateOrderNumber(),
                'customer_id' => $customerId,
                'user_id' => $staffId,
                'order_date' => now()->toDateString(),
                'status' => $status,
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => ['Each order item must have a quantity greater than zero.'],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $subtotal = $quantity * $unitPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total_amount' => $total]);

            return $order->fresh(['customer', 'staff', 'items.product']);
        });
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (static::query()->where('order_number', $number)->exists());

        return $number;
    }
}
