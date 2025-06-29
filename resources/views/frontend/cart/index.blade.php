{{-- resources/views/frontend/cart/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

        @if ($cart->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden">
                <!-- Cart Items -->
                <div class="divide-y divide-gray-200">
                    @foreach ($cart as $key => $item)
                        <div class="p-6 flex items-center space-x-4">
                            <!-- Product Image -->
                            <div class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-lg overflow-hidden">
                                @if ($item['image'])
                                    <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}"
                                        class="w-full h-full object-cover">
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

                            <!-- Product Details -->
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">{{ $item['name'] }}</h3>
                                <p class="text-gray-600">${{ number_format($item['price'], 2) }}</p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center space-x-2">
                                <button onclick="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-50"
                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                        </path>
                                    </svg>
                                </button>

                                <span class="w-12 text-center font-medium">{{ $item['quantity'] }}</span>

                                <button onclick="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Item Total -->
                            <div class="text-right">
                                <p class="font-medium text-gray-900">
                                    ${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                <button onclick="removeItem('{{ $key }}')"
                                    class="text-red-600 hover:text-red-700 text-sm mt-1">
                                    Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Cart Summary -->
                <div class="bg-gray-50 p-6">
                    <div class="flex justify-between items-center text-lg font-medium text-gray-900 mb-4">
                        <span>Total</span>
                        <span>${{ number_format($total->value, 2) }}</span>
                    </div>

                    <div class="space-y-3">
                        <button class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">
                            Proceed to Checkout
                        </button>
                        <a href="{{ route('products.index') }}"
                            class="block w-full bg-gray-200 text-gray-800 py-3 rounded-lg hover:bg-gray-300 font-medium text-center">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path
                        d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0124 24c4.21 0 7.863 2.613 9.288 6.286"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Add some products to get started!</p>
                <a href="{{ route('products.index') }}"
                    class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    Start Shopping
                </a>
            </div>
        @endif
    </div>

    <script>
        function updateQuantity(key, quantity) {
            if (quantity < 1) return;

            fetch(`/cart/${key}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Simple reload for now
                    }
                });
        }

        function removeItem(key) {
            fetch(`/cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }
    </script>
@endsection
