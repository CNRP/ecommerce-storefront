<div class="fixed inset-0 z-50 overflow-hidden" x-show="$store.cart.isOpen" x-cloak
    @keydown.escape.window="$store.cart.close()">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="$store.cart.isOpen"
        x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="$store.cart.close()"></div>

    <!-- Sidebar -->
    <div class="absolute right-0 top-0 h-full w-full max-w-md">
        <div class="flex h-full flex-col bg-white shadow-xl" x-show="$store.cart.isOpen"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">

            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-6 border-b border-gray-300">
                <h2 class="text-lg font-medium text-gray-900">Shopping Cart</h2>
                <button @click="$store.cart.close()" class="rounded-md bg-white text-gray-400 hover:text-gray-500 p-1">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto px-4 py-6">
                <!-- Empty State -->
                <template x-if="$store.cart.items.length === 0">
                    <div class="text-center text-gray-500 py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                            viewBox="0 0 48 48">
                            <path
                                d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0124 24c4.21 0 7.863 2.613 9.288 6.286"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2">Your cart is empty</p>
                        <a href="{{ route('products.index') }}" @click="$store.cart.close()"
                            class="mt-4 inline-block text-blue-600 hover:text-blue-700">
                            Continue Shopping
                        </a>
                    </div>
                </template>

                <!-- Cart Items List -->
                <div class="space-y-4">
                    <template x-for="item in $store.cart.items" :key="item.key">
                        <div class="flex items-center space-x-3 py-3 border-b border-gray-100 last:border-b-0">
                            <!-- Product Image -->
                            <div class="flex-shrink-0 w-12 h-12 bg-gray-200 rounded overflow-hidden">
                                <template x-if="item.image">
                                    <img :src="item.image" :alt="item.name"
                                        class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate" x-text="item.name"></h4>
                                <p class="text-sm text-gray-500">
                                    Qty: <span x-text="item.quantity"></span>
                                </p>
                                <p class="text-sm font-medium text-gray-900"
                                    x-text="'£' + (item.price * item.quantity).toFixed(2)"></p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center space-x-1">
                                <button @click="$store.cart.updateQuantity(item.key, item.quantity - 1)"
                                    :disabled="item.quantity <= 1"
                                    class="w-6 h-6 flex items-center justify-center border border-gray-300 rounded text-xs hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    -
                                </button>
                                <span class="w-8 text-center text-sm" x-text="item.quantity"></span>
                                <button @click="$store.cart.updateQuantity(item.key, item.quantity + 1)"
                                    :disabled="item.quantity >= 10"
                                    class="w-6 h-6 flex items-center justify-center border border-gray-300 rounded text-xs hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    +
                                </button>
                            </div>

                            <!-- Remove Button -->
                            <button @click="$store.cart.removeItem(item.key)"
                                class="text-red-500 hover:text-red-700 p-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer -->
            <template x-if="$store.cart.items.length > 0">
                <div class="border-t border-gray-200 px-4 py-6">
                    <div class="flex justify-between text-base font-medium text-gray-900 mb-4">
                        <p>Subtotal</p>
                        <p x-text="$store.cart.formattedTotal"></p>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">Shipping and taxes calculated at checkout.</p>

                    <div class="mt-6 space-y-3">
                        <a href="{{ route('cart.index') }}" @click="$store.cart.close()"
                            class="block w-full rounded-md bg-gray-600 px-6 py-3 text-center text-base font-medium text-white hover:bg-gray-700 transition-colors">
                            View Cart
                        </a>
                        <a href="{{ route('checkout.index') }}" @click="$store.cart.close()"
                            class="block w-full rounded-md bg-blue-600 px-6 py-3 text-center text-base font-medium text-white hover:bg-blue-700 transition-colors">
                            Checkout
                        </a>
                    </div>

                    <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                        <p>
                            or
                            <button @click="$store.cart.close()" class="font-medium text-blue-600 hover:text-blue-500">
                                Continue Shopping<span aria-hidden="true"> →</span>
                            </button>
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
