<div
    class="bg-white rounded-lg hover:scale-105 transition-transform border border-gray-300 overflow-hidden h-full flex flex-col">
    <a href="{{ route('products.show', $product->slug) }}">
        <div class="aspect-square bg-gray-200 relative overflow-hidden">
            @if ($product->getMainImage())
                <img src="{{ Storage::url($product->getMainImage()) }}" alt="{{ $product->name }}"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            @endif

            @if ($product->getDiscountPercentage())
                <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                    -{{ $product->getDiscountPercentage() }}%
                </div>
            @endif

            @if ($product->is_featured)
                <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                    Featured
                </div>
            @endif
        </div>
    </a>

    <div class="p-4 flex flex-col flex-grow">
        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-blue-600">
                {{ $product->name }}
            </a>
        </h3>

        <p class="text-gray-600 text-sm mb-3 line-clamp-2 flex-grow">{{ $product->short_description }}</p>

        <!-- Stock indicator -->
        @if (
            $product->track_inventory &&
                $product->inventory_quantity <= $product->low_stock_threshold &&
                $product->inventory_quantity > 0)
            <p class="text-orange-600 text-xs mb-3">Only {{ $product->inventory_quantity }} left!</p>
        @endif

        <!-- Price and Button Section - Always at bottom -->
        <div class="mt-auto">
            <div class="flex justify-between items-end mb-3">
                <!-- Price Section - Always Stacked -->
                <div class="flex flex-col">
                    <span class="font-bold text-lg text-gray-900">£{{ number_format($product->price, 2) }}</span>
                    @if ($product->compare_price && $product->compare_price > $product->price)
                        <span
                            class="text-sm text-gray-500 line-through">£{{ number_format($product->compare_price, 2) }}</span>
                    @endif
                </div>
            </div>

            <!-- NEW: Component-based Add to Cart Button -->
            @if ($product->isInStock() && !$product->hasVariants())
                <x-cart.add-to-cart-button :item="$product" size="sm" class="w-full" />
            @else
                <a href="{{ route('products.show', $product->slug) }}"
                    class="inline-block w-full px-3 py-1 bg-blue-600 text-white text-sm text-center rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    @if ($product->hasVariants())
                        View Options
                    @else
                        Out of Stock
                    @endif
                </a>
            @endif
        </div>
    </div>
</div>
