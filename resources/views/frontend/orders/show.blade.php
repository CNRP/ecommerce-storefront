{{-- resources/views/frontend/orders/show.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Order ' . $order->order_number . ' - ' . config('app.name'))

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Order {{ $order->order_number }}</h1>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <span>Placed on {{ $order->created_at->format('M j, Y \a\t g:i A') }}</span>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if ($order->status === 'completed') bg-green-100 text-green-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'fulfilled') bg-purple-100 text-purple-800
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                            @elseif($order->status === 'pending_payment') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                        @if ($order->payment_status === 'succeeded')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Paid
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    @auth
                        <a href="{{ route('orders.index') }}"
                            class="bg-gray-200 text-gray-800 text-center py-2 px-4 rounded-md font-medium hover:bg-gray-300 transition-colors">
                            ← Back to Orders
                        </a>
                    @else
                        <a href="{{ route('orders.track') }}"
                            class="bg-gray-200 text-gray-800 text-center py-2 px-4 rounded-md font-medium hover:bg-gray-300 transition-colors">
                            ← Track Another Order
                        </a>
                    @endauth

                    @if ($order->canBeCancelled())
                        <button onclick="confirmCancellation()"
                            class="bg-red-600 text-white text-center py-2 px-4 rounded-md font-medium hover:bg-red-700 transition-colors">
                            Cancel Order
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Order Status Timeline -->
                @if ($order->statusHistories->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Order Status</h2>

                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach ($order->statusHistories as $history)
                                    <li>
                                        <div class="relative pb-8 @if ($loop->last) pb-0 @endif">
                                            @if (!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                    aria-hidden="true"></span>
                                            @endif

                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span
                                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                        @if ($history->to_status === 'completed') bg-green-500
                                                        @elseif($history->to_status === 'cancelled') bg-red-500
                                                        @elseif($history->to_status === 'processing') bg-blue-500
                                                        @elseif($history->to_status === 'fulfilled') bg-purple-500
                                                        @else bg-gray-400 @endif">
                                                        @if ($history->to_status === 'completed')
                                                            <svg class="w-5 h-5 text-white" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        @elseif($history->to_status === 'cancelled')
                                                            <svg class="w-5 h-5 text-white" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        @elseif($history->to_status === 'fulfilled')
                                                            <svg class="w-5 h-5 text-white" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                                <path
                                                                    d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z" />
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5 text-white" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $history->getStatusChangeDescription() }}
                                                        </p>
                                                        @if ($history->notes)
                                                            <p class="text-sm text-gray-500 mt-1">{{ $history->notes }}</p>
                                                        @endif
                                                        @if ($history->user)
                                                            <p class="text-xs text-gray-400 mt-1">Updated by
                                                                {{ $history->getUserName() }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time>{{ $history->created_at->format('M j, g:i A') }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-300  p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Order Items</h2>

                    <div class="space-y-6">
                        @foreach ($order->items as $item)
                            <div class="flex items-start space-x-4 py-4 border-b border-gray-200 last:border-b-0 last:pb-0">
                                <div class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-lg overflow-hidden">
                                    @if ($item->product_image)
                                        <img src="{{ Storage::url($item->product_image) }}"
                                            alt="{{ $item->getDisplayName() }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $item->getDisplayName() }}</h3>
                                    <p class="text-sm text-gray-500 mb-2">SKU: {{ $item->getSku() }}</p>

                                    @if ($item->variant_attributes)
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            @foreach ($item->variant_attributes as $attribute => $value)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $attribute }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($item->product_description)
                                        <p class="text-sm text-gray-600 mb-3">{{ $item->product_description }}</p>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="text-gray-500">Qty: {{ $item->quantity }}</span>
                                        <span class="text-gray-500">Unit Price:
                                            {{ $item->getUnitPriceMoney()->format() }}</span>
                                        @if ($item->compare_price && $item->compare_price > $item->unit_price)
                                            <span
                                                class="text-gray-400 line-through">{{ Money::fromCents($item->compare_price, $order->currency)->format() }}</span>
                                            <span
                                                class="text-green-600 text-xs font-medium">{{ $item->getDiscountPercentage() }}%
                                                off</span>
                                        @endif
                                    </div>

                                    @if ($item->quantity_fulfilled > 0)
                                        <div class="mt-2 text-sm">
                                            <span class="text-green-600 font-medium">{{ $item->quantity_fulfilled }}
                                                fulfilled</span>
                                            @if ($item->getQuantityPending() > 0)
                                                <span class="text-gray-500"> • {{ $item->getQuantityPending() }}
                                                    pending</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <p class="text-lg font-medium text-gray-900">{{ $item->getLineTotalMoney()->format() }}
                                    </p>
                                    @if ($item->tax_amount > 0)
                                        <p class="text-sm text-gray-500">Inc. {{ $item->getTaxMoney()->format() }} VAT</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Addresses -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Billing Address -->
                    <div class="bg-white rounded-lg shadow-sm border  border-gray-300  p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Address</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p class="font-medium text-gray-900">
                                {{ $order->billing_address['first_name'] }} {{ $order->billing_address['last_name'] }}
                            </p>
                            @if ($order->billing_address['company'])
                                <p>{{ $order->billing_address['company'] }}</p>
                            @endif
                            <p>{{ $order->billing_address['address_line_1'] }}</p>
                            @if ($order->billing_address['address_line_2'])
                                <p>{{ $order->billing_address['address_line_2'] }}</p>
                            @endif
                            <p>{{ $order->billing_address['city'] }}, {{ $order->billing_address['state_county'] }}
                                {{ $order->billing_address['postal_code'] }}</p>
                            <p>{{ $order->billing_address['country'] }}</p>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Address</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p class="font-medium text-gray-900">
                                {{ $order->shipping_address['first_name'] }} {{ $order->shipping_address['last_name'] }}
                            </p>
                            @if ($order->shipping_address['company'])
                                <p>{{ $order->shipping_address['company'] }}</p>
                            @endif
                            <p>{{ $order->shipping_address['address_line_1'] }}</p>
                            @if ($order->shipping_address['address_line_2'])
                                <p>{{ $order->shipping_address['address_line_2'] }}</p>
                            @endif
                            <p>{{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state_county'] }}
                                {{ $order->shipping_address['postal_code'] }}</p>
                            <p>{{ $order->shipping_address['country'] }}</p>
                        </div>

                        @if ($order->status === 'fulfilled' && $order->shipped_at)
                            <div class="mt-4 pt-4 border-gray-300  border-t">
                                <p class="text-sm font-medium text-gray-900">Shipping Information</p>
                                <p class="text-sm text-gray-600">Shipped on {{ $order->shipped_at->format('M j, Y') }}</p>
                                @if ($order->estimated_delivery_date)
                                    <p class="text-sm text-gray-600">Estimated delivery:
                                        {{ $order->estimated_delivery_date->format('M j, Y') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if ($order->customer_notes)
                    <!-- Customer Notes -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-yellow-800 mb-2">Order Notes</h3>
                        <p class="text-sm text-yellow-700">{{ $order->customer_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-24">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>

                    <!-- Order Totals -->
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal ({{ $order->getTotalItems() }}
                                {{ Str::plural('item', $order->getTotalItems()) }})</span>
                            <span>{{ $order->getSubtotalMoney()->format() }}</span>
                        </div>

                        @if ($order->tax_amount > 0)
                            <div class="flex justify-between">
                                <span>VAT ({{ number_format($order->tax_rate * 100, 1) }}%)</span>
                                <span>{{ $order->getTaxMoney()->format() }}</span>
                            </div>
                        @endif

                        @if ($order->shipping_amount > 0)
                            <div class="flex justify-between">
                                <span>Shipping</span>
                                <span>{{ $order->getShippingMoney()->format() }}</span>
                            </div>
                        @else
                            <div class="flex justify-between text-green-600">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                        @endif

                        @if ($order->discount_amount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span>-{{ $order->getDiscountMoney()->format() }}</span>
                            </div>
                        @endif

                        <div class="border-t pt-3 flex justify-between font-medium text-lg">
                            <span>Total</span>
                            <span>{{ $order->getTotalMoney()->format() }}</span>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Payment Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Payment Status</span>
                                <span
                                    class="font-medium
                                    @if ($order->payment_status === 'succeeded') text-green-600
                                    @elseif($order->payment_status === 'pending') text-yellow-600
                                    @elseif($order->payment_status === 'failed') text-red-600
                                    @else text-gray-600 @endif">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>

                            @if ($order->payments->where('status', 'succeeded')->first())
                                @php $payment = $order->payments->where('status', 'succeeded')->first(); @endphp
                                <div class="flex justify-between">
                                    <span>Payment Method</span>
                                    <span>{{ $payment->getPaymentMethodDescription() }}</span>
                                </div>
                                @if ($payment->processed_at)
                                    <div class="flex justify-between">
                                        <span>Paid On</span>
                                        <span>{{ $payment->processed_at->format('M j, Y') }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Customer Information</h3>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p>{{ $order->customer_details['first_name'] }} {{ $order->customer_details['last_name'] }}
                            </p>
                            <p>{{ $order->customer_details['email'] }}</p>
                            @if ($order->customer_details['phone'])
                                <p>{{ $order->customer_details['phone'] }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 pt-6 border-t space-y-3">
                        @if ($order->status === 'completed')
                            <button
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md font-medium hover:bg-blue-700 transition-colors">
                                Reorder Items
                            </button>
                        @endif

                        @if ($order->canBeCancelled())
                            <button onclick="confirmCancellation()"
                                class="w-full bg-red-600 text-white py-2 px-4 rounded-md font-medium hover:bg-red-700 transition-colors">
                                Cancel Order
                            </button>
                        @endif

                        <a href="{{ route('products.index') }}"
                            class="block w-full bg-gray-200 text-gray-800 text-center py-2 px-4 rounded-md font-medium hover:bg-gray-300 transition-colors">
                            Continue Shopping
                        </a>
                    </div>

                    <!-- Contact Support -->
                    <div class="mt-6 pt-6 border-t text-center">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Need Help?</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            Contact our support team for assistance with your order.
                        </p>
                        <a href="mailto:support@example.com"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            support@example.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($order->canBeCancelled())
        <!-- Cancel Order Modal -->
        <div id="cancel-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" x-data="{ open: false }" x-show="open"
            x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="{{ route('orders.cancel', $order->order_number) }}">
                        @csrf
                        @guest
                            <input type="hidden" name="token" value="{{ $order->guest_token }}">
                        @endguest

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Order</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to cancel order {{ $order->order_number }}? This action
                                            cannot be undone and any payment will be refunded within 3-5 business days.
                                        </p>
                                        <div class="mt-4">
                                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason
                                                for cancellation (optional)</label>
                                            <textarea name="reason" id="reason" rows="3"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                placeholder="Please let us know why you're cancelling..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel Order
                            </button>
                            <button type="button" @click="open = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Keep Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function confirmCancellation() {
                const modal = document.getElementById('cancel-modal');
                modal.style.display = 'block';
                // Trigger Alpine.js to show modal
                modal.querySelector('[x-data]').__x.$data.open = true;
            }
        </script>
    @endif
@endsection
