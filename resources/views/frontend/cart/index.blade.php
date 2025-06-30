{{-- resources/views/frontend/cart/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('content')
    <div x-data class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

        <template x-if="$store.cart.items.length > 0">
            <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden">
                <!-- Cart Items -->
                <div class="divide-y divide-gray-200">
                    <template x-for="item in $store.cart.items" :key="item.key">
                        <div class="p-6 flex items-center space-x-4">
                            <!-- Product Image -->
                            <div class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-lg overflow-hidden">
                                <template x-if="item.image">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900" x-text="item.name"></h3>
                                <p class="text-gray-600" x-text="'$' + item.price.toFixed(2)"></p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center space-x-2">
                                <button @click="$store.cart.updateQuantity(item.key, item.quantity - 1)"
                                    :disabled="item.quantity <= 1"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                        </path>
                                    </svg>
                                </button>

                                <span class="w-12 text-center font-medium" x-text="item.quantity"></span>

                                <button @click="$store.cart.updateQuantity(item.key, item.quantity + 1)"
                                    :disabled="item.quantity >= 10"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Item Total -->
                            <div class="text-right">
                                <p class="font-medium text-gray-900" x-text="'$' + (item.price * item.quantity).toFixed(2)">
                                </p>
                                <button @click="$store.cart.removeItem(item.key)"
                                    class="text-red-600 hover:text-red-700 text-sm mt-1">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Cart Summary -->
                <div class="bg-gray-50 p-6">
                    <div class="flex justify-between items-center text-lg font-medium text-gray-900 mb-4">
                        <span>Total</span>
                        <span x-text="$store.cart.formattedTotal"></span>
                    </div>

                    <div class="space-y-3 flex flex-col">
                        <a href="{{ route('checkout.index') }}"
                            class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-500 font-medium text-center"
                            @click="$store.cart.close()">
                            Proceed to Checkout
                        </a>
                        <a href="{{ route('products.index') }}"
                            class="block w-full bg-gray-200 text-gray-800 py-3 rounded-lg hover:bg-gray-300 font-medium text-center"
                            @click="$store.cart.close()">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="$store.cart.items.length === 0">
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
        </template>
    </div>
@endsection
