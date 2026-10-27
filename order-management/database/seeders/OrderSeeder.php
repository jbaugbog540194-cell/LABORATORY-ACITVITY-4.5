<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::query()->firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Member',
                'password' => bcrypt('password123'),
                'is_staff' => true,
            ]
        );

        $categories = [
            'Coffee',
            'Pastries',
            'Breakfast',
        ];

        $categoryRecords = [];
        foreach ($categories as $categoryName) {
            $categoryRecords[$categoryName] = Category::query()->firstOrCreate([
                'name' => $categoryName,
            ], [
                'description' => 'Fresh cafe ' . strtolower($categoryName) . '.',
            ]);
        }

        $products = [
            ['category' => 'Coffee', 'name' => 'Espresso', 'description' => 'Strong espresso shot', 'price' => 3.50, 'stock_quantity' => 25],
            ['category' => 'Coffee', 'name' => 'Latte', 'description' => 'Creamy coffee with milk', 'price' => 5.25, 'stock_quantity' => 18],
            ['category' => 'Coffee', 'name' => 'Cappuccino', 'description' => 'Foamy cappuccino', 'price' => 5.75, 'stock_quantity' => 20],
            ['category' => 'Pastries', 'name' => 'Croissant', 'description' => 'Buttery pastry', 'price' => 4.00, 'stock_quantity' => 30],
            ['category' => 'Pastries', 'name' => 'Blueberry Muffin', 'description' => 'Soft and sweet muffin', 'price' => 4.50, 'stock_quantity' => 22],
            ['category' => 'Breakfast', 'name' => 'Bagel Sandwich', 'description' => 'Savory breakfast sandwich', 'price' => 7.50, 'stock_quantity' => 15],
        ];

        $productMap = [];
        foreach ($products as $productData) {
            $product = Product::query()->firstOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id' => $categoryRecords[$productData['category']]->id,
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    'is_available' => true,
                ]
            );
            $productMap[$productData['name']] = $product;
        }

        $customers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '0912345678', 'address' => 'Main Street'],
            ['name' => 'Brian Smith', 'email' => 'brian@example.com', 'phone' => '0987654321', 'address' => 'Oak Avenue'],
            ['name' => 'Carla Gomez', 'email' => 'carla@example.com', 'phone' => '0999888777', 'address' => 'Hill Road'],
        ];

        $customerModels = [];
        foreach ($customers as $customerData) {
            $customer = Customer::query()->firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
            $customerModels[] = $customer;
        }

        $orderPayloads = [
            [
                'customer' => $customerModels[0],
                'status' => 'pending',
                'items' => [
                    ['product' => 'Latte', 'quantity' => 2],
                    ['product' => 'Croissant', 'quantity' => 1],
                ],
            ],
            [
                'customer' => $customerModels[1],
                'status' => 'paid',
                'items' => [
                    ['product' => 'Cappuccino', 'quantity' => 2],
                    ['product' => 'Blueberry Muffin', 'quantity' => 2],
                ],
            ],
            [
                'customer' => $customerModels[2],
                'status' => 'completed',
                'items' => [
                    ['product' => 'Bagel Sandwich', 'quantity' => 1],
                    ['product' => 'Espresso', 'quantity' => 2],
                ],
            ],
        ];

        foreach ($orderPayloads as $orderIndex => $payload) {
            $items = [];
            foreach ($payload['items'] as $item) {
                $items[] = [
                    'product_id' => $productMap[$item['product']]->id,
                    'quantity' => $item['quantity'],
                ];
            }

            $order = Order::createWithItems(
                $payload['customer']->id,
                $staff->id,
                $items,
                $payload['status'],
                'ORD-' . Str::upper(Str::random(6))
            );

            if ($orderIndex === 0) {
                Payment::recordForOrder($order->id, [
                    'payment_reference' => 'PAY-' . Str::upper(Str::random(6)),
                    'payment_method' => 'cash',
                    'amount' => $order->total_amount,
                    'paid_at' => now(),
                ]);
            }

            if ($orderIndex === 1) {
                Payment::recordForOrder($order->id, [
                    'payment_reference' => 'PAY-' . Str::upper(Str::random(6)),
                    'payment_method' => 'card',
                    'amount' => 10.00,
                    'paid_at' => now(),
                ]);

                Payment::recordForOrder($order->id, [
                    'payment_reference' => 'PAY-' . Str::upper(Str::random(6)),
                    'payment_method' => 'cash',
                    'amount' => $order->total_amount - 10.00,
                    'paid_at' => now(),
                ]);
            }
        }
    }
}
