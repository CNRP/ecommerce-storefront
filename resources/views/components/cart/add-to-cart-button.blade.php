@props(['item', 'quantity' => 1, 'size' => 'md', 'showQuantitySelector' => false, 'variant' => 'primary'])

<div class="add-to-cart-component" x-data="addToCartComponent({{ $getProductId() }}, {{ $getVariantId() ?? 'null' }}, {{ $quantity }})">

    @if ($showQuantitySelector)
        <div class="flex items-center space-x-2 mb-3">
            <label class="text-sm font-medium text-gray-700">Quantity:</label>
            <div class="flex items-center border border-gray-300 rounded">
                <button @click="decreaseQuantity()" :disabled="quantity <= 1"
                    class="px-2 py-1 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <input type="number" x-model="quantity" min="1" max="10"
                    class="w-16 px-2 py-1 text-center border-0 focus:ring-0 focus:outline-none">
                <button @click="increaseQuantity()" :disabled="quantity >= 10"
                    class="px-2 py-1 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if ($canAddToCart())
        <button @click="addToCart()" :disabled="loading" {{ $attributes->merge(['class' => $getButtonClasses()]) }}>

            <!-- Loading Spinner -->
            <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>

            <span x-show="!loading">Add to Cart</span>
            <span x-show="loading">Adding...</span>
        </button>
    @else
        <button disabled
            {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-4 py-2 bg-gray-400 text-white font-medium rounded-md cursor-not-allowed']) }}>
            @if ($item instanceof \App\Models\Product\ProductVariant && !$item->is_active)
                Unavailable
            @elseif($item instanceof \App\Models\Product\Product && $item->status !== 'published')
                Unavailable
            @else
                Out of Stock
            @endif
        </button>
    @endif
</div>
