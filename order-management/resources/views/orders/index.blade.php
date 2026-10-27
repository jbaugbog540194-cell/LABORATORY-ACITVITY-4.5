@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Orders</h1>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->order_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->customer->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->staff->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format((float) $order->total_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($order->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
