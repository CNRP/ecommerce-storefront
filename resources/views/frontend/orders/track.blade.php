{{-- resources/views/frontend/orders/track.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Track Your Order - ' . config('app.name'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Track Your Order</h1>
            <p class="text-gray-600">Enter your order details to track your package</p>
        </div>

        @if (!$order)
            <!-- Order Tracking Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
                <form method="POST" action="{{ route('orders.track.submit') }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="order_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Order Number
                            </label>
                            <input type="text" id="order_number" name="order_number" value="{{ old('order_number') }}"
                                placeholder="e.g., ORD-2024-123456" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('order_number') border-red-300 @enderror">
                            @error('order_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="The email address used for your order" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 transition-colors">
                            Track Order
                        </button>
                    </div>
                </form>

                <!-- Help Text -->
                <div class="mt-6 pt-6 border-t text-center">
                    <p class="text-sm text-gray-600 mb-2">
                        <strong>Need help finding your order?</strong>
                    </p>
                    <p class="text-sm text-gray-500">
                        Check your email for the order confirmation that contains your order number.
                    </p>
                </div>
            </div>

            <!-- Guest Login Alternative -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Have an account?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                        Sign in to view all your orders
                    </a>
                </p>
            </div>
        @else
            <!-- Order Found - Display Tracking -->
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Order {{ $order->order_number }}</h2>
                        <p class="text-sm text-gray-600">Placed on {{ $order->created_at->format('M j, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if ($order->status === 'completed') bg-green-100 text-green-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'fulfilled') bg-purple-100 text-purple-800
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                </div>

                <!-- Order Status Timeline -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Status</h3>

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
                                                        <p class="text-sm text-gray-500">{{ $history->notes }}</p>
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

                <!-- Order Items Summary -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Items in this order</h3>

                    <div class="space-y-4">
                        @foreach ($order->items as $item)
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-gray-200 rounded overflow-hidden">
                                    @if ($item->product_image)
                                        <img src="{{ Storage::url($item->product_image) }}"
                                            alt="{{ $item->getDisplayName() }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->getDisplayName() }}</p>
                                    <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                                </div>

                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item->getLineTotalMoney()->format() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Total -->
                <div class="border-t pt-4 mt-6">
                    <div class="flex justify-between text-lg font-medium">
                        <span>Total</span>
                        <span>{{ $order->getTotalMoney()->format() }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('orders.show', ['orderNumber' => $order->order_number, 'token' => $order->guest_token]) }}"
                    class="flex-1 bg-blue-600 text-white text-center py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    View Full Order Details
                </a>

                @if ($order->canBeCancelled())
                    <button onclick="confirmCancellation('{{ $order->order_number }}')"
                        class="flex-1 bg-red-600 text-white text-center py-3 rounded-lg font-medium hover:bg-red-700 transition-colors">
                        Cancel Order
                    </button>
                @endif

                <a href="{{ route('orders.track') }}"
                    class="flex-1 bg-gray-200 text-gray-800 text-center py-3 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Track Another Order
                </a>
            </div>
        @endif
    </div>

    @if ($order && $order->canBeCancelled())
        <!-- Cancel Order Modal -->
        <div id="cancel-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" x-data="{ open: false }" x-show="open"
            x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="{{ route('orders.cancel', $order->order_number) }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $order->guest_token }}">

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
                                            cannot be undone.
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
            function confirmCancellation(orderNumber) {
                Alpine.data('cancel-modal', () => ({
                    open: true
                }));
                document.getElementById('cancel-modal').style.display = 'block';
            }
        </script>
    @endif
@endsection
