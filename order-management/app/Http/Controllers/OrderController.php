<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->with(['customer', 'staff'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        return view('orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = Order::createWithItems(
            (int) $validated['customer_id'],
            Auth::id(),
            collect($validated['items'])->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ])->all()
        );

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }
}
