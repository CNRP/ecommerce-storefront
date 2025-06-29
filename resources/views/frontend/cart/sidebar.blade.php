{{-- resources/views/frontend/cart/sidebar.blade.php --}}
<!-- Cart Sidebar -->
<div x-show="cartOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" @keydown.escape.window="cartOpen = false">
    <!-- Backdrop -->
    <div x-show="cartOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="cartOpen = false"></div>

    <!-- Sidebar -->
    <div class="absolute right-0 top-0 h-full w-full max-w-md">
        <div x-show="cartOpen" x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full" class="flex h-full flex-col bg-white shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-6 border-b border-gray-300">
                <h2 class="text-lg font-medium text-gray-900">Shopping Cart</h2>
                <button @click="cartOpen = false" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Content -->
            <div class="flex-1 overflow-y-auto px-4 py-6">
                @php
                    $cartService = app(\App\Services\CartService::class);
                    $cart = $cartService->getCart();
                    $total = $cartService->getTotal();
                @endphp

                @if ($cart->count() > 0)
                    <div class="space-y-4">
                        @foreach ($cart as $key => $item)
                            <div class="flex items-center space-x-3 py-3 border-b border-gray-100">
                                <!-- Product Image -->
                                <div class="flex-shrink-0 w-12 h-12 bg-gray-200 rounded overflow-hidden">
                                    @if ($item['image'])
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}"
                                            class="w-full h-full object-cover">
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

                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        ${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                </div>

                                <!-- Remove Button -->
                                <button onclick="removeFromCartSidebar('{{ $key }}')"
                                    class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                            viewBox="0 0 48 48">
                            <path
                                d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0124 24c4.21 0 7.863 2.613 9.288 6.286"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2">Your cart is empty</p>
                        <a href="{{ route('products.index') }}"
                            class="mt-4 inline-block text-blue-600 hover:text-blue-700">
                            Continue Shopping
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            @if ($cart->count() > 0)
                <div class="border-t border-gray-200 px-4 py-6">
                    <div class="flex justify-between text-base font-medium text-gray-900 mb-4">
                        <p>Subtotal</p>
                        <p>${{ number_format($total->value, 2) }}</p>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('cart.index') }}"
                            class="block w-full rounded-md bg-gray-600 px-6 py-3 text-center text-base font-medium text-white hover:bg-gray-700">
                            View Cart
                        </a>
                        <button
                            class="block w-full rounded-md bg-blue-600 px-6 py-3 text-center text-base font-medium text-white hover:bg-blue-700">
                            Checkout
                        </button>
                    </div>
                    <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                        <p>
                            or
                            <button @click="cartOpen = false" class="font-medium text-blue-600 hover:text-blue-500">
                                Continue Shopping<span aria-hidden="true"> →</span>
                            </button>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function removeFromCartSidebar(key) {
        fetch(`/cart/${key}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;

                    // Reload the page to refresh cart sidebar content
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
            });
    }
</script>
