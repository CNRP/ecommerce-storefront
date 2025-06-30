{{-- resources/views/frontend/checkout/confirmation.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Order Confirmation - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
            <p class="text-lg text-gray-600">Thank you for your purchase. Your order has been successfully placed.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Order Number</p>
                            <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Order Date</p>
                            <p class="font-medium text-gray-900">{{ $order->created_at->format('M j, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Amount</p>
                            <p class="font-medium text-gray-900">{{ $order->getTotalMoney()->format() }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Payment Status</p>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Paid
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Items</h2>

                    <div class="space-y-4">
                        @foreach ($order->items as $item)
                            <div class="flex items-center space-x-4 py-4 border-b border-gray-200 last:border-b-0">
                                <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
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
                                    <h3 class="font-medium text-gray-900">{{ $item->getDisplayName() }}</h3>
                                    <p class="text-sm text-gray-500">SKU: {{ $item->getSku() }}</p>
                                    @if ($item->variant_attributes)
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach ($item->variant_attributes as $attribute => $value)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $attribute }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                                    <p class="font-medium text-gray-900">{{ $item->getLineTotalMoney()->format() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Addresses -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Billing Address -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
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
                    <div class="bg-white rounded-lg shadow-sm border border-gray-300  p-6">
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
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6 sticky top-24">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>{{ $order->getSubtotalMoney()->format() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>VAT</span>
                            <span>{{ $order->getTaxMoney()->format() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping</span>
                            <span>{{ $order->getShippingMoney()->format() }}</span>
                        </div>
                        @if ($order->discount_amount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span>-{{ $order->getDiscountMoney()->format() }}</span>
                            </div>
                        @endif
                        <div class="border-t border-gray-300  pt-3 flex justify-between font-medium text-lg">
                            <span>Total</span>
                            <span>{{ $order->getTotalMoney()->format() }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('orders.show', ['orderNumber' => $order->order_number, 'token' => $order->guest_token]) }}"
                            class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            View Order Details
                        </a>
                        <a href="{{ route('products.index') }}"
                            class="block w-full bg-gray-200 text-gray-800 text-center py-3 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                            Continue Shopping
                        </a>
                    </div>

                    <!-- Contact Information -->
                    <div class="mt-6 pt-6 border-t border-gray-300  text-center">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Need Help?</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            We'll send you shipping and tracking information via email to
                            <strong>{{ $order->customer->email }}</strong>
                        </p>
                        <p class="text-sm text-gray-600">
                            Questions? Contact us at
                            <a href="mailto:support@example.com" class="text-blue-600 hover:text-blue-700">
                                support@example.com
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if ($order->customer_notes)
            <!-- Customer Notes -->
            <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-yellow-800 mb-2">Order Notes</h3>
                <p class="text-sm text-yellow-700">{{ $order->customer_notes }}</p>
            </div>
        @endif
    </div>
@endsection
