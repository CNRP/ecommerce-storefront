{{-- resources/views/frontend/products/show.blade.php --}}
@extends('layouts.frontend')

@section('title', $product->name . ' - ' . config('app.name'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8">

            <!-- Product Images -->
            <div class="mb-8 lg:mb-0">
                <!-- Main Image -->
                <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden mb-4">
                    @if ($product->getMainImage())
                        <img src="{{ Storage::url($product->getMainImage()) }}" alt="{{ $product->name }}"
                            class="w-full h-full object-cover" id="main-product-image">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Thumbnail Gallery -->
                @if ($product->gallery && count($product->gallery) > 0)
                    <div class="grid grid-cols-4 gap-2">
                        @if ($product->image)
                            <button onclick="changeMainImage('{{ Storage::url($product->image) }}')"
                                class="aspect-square bg-gray-200 rounded overflow-hidden border-2 border-blue-500">
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover">
                            </button>
                        @endif

                        @foreach ($product->gallery as $image)
                            <button onclick="changeMainImage('{{ Storage::url($image) }}')"
                                class="aspect-square bg-gray-200 rounded overflow-hidden border-2 border-transparent hover:border-blue-500">
                                <img src="{{ Storage::url($image) }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="lg:pl-8">
                <!-- Product Title -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                </div>

                <!-- Product Variants - Flexible Attribute System -->
                <!-- Product Variants - Completely Dynamic -->
                @if ($product->hasVariants())
                    <div class="mb-6" x-data="{
                        selectedAttributes: {},
                        selectedVariant: null,
                        availableVariants: {{ $product->variants->load('attributeValues.attribute')->toJson() }},
                        attributes: {},
                        lowStockThreshold: {{ $product->low_stock_threshold ?? 5 }},
                    
                        init() {
                            // Group attributes dynamically with full metadata
                            this.availableVariants.forEach(variant => {
                                variant.attribute_values.forEach(av => {
                                    const attr = av.attribute;
                                    if (!this.attributes[attr.name]) {
                                        this.attributes[attr.name] = {
                                            name: attr.name,
                                            display_type: attr.display_type,
                                            sort_order: attr.sort_order,
                                            values: new Map()
                                        };
                                    }
                                    // Store both value and color_code
                                    this.attributes[attr.name].values.set(av.value, {
                                        value: av.value,
                                        color_code: av.color_code,
                                        label: av.label || av.value
                                    });
                                });
                            });
                    
                            // Convert Maps to Arrays and sort by attribute sort_order
                            const sortedAttributes = {};
                            Object.entries(this.attributes)
                                .sort(([, a], [, b]) => a.sort_order - b.sort_order)
                                .forEach(([name, data]) => {
                                    sortedAttributes[name] = {
                                        ...data,
                                        values: Array.from(data.values.values())
                                    };
                                });
                            this.attributes = sortedAttributes;
                        },
                    
                        selectAttribute(attributeName, value) {
                            this.selectedAttributes[attributeName] = value;
                            this.updateSelectedVariant();
                        },
                    
                        updateSelectedVariant() {
                            const requiredAttributes = Object.keys(this.attributes);
                            const selectedAttributeKeys = Object.keys(this.selectedAttributes);
                    
                            const allSelected = requiredAttributes.every(attr =>
                                selectedAttributeKeys.includes(attr) && this.selectedAttributes[attr]
                            );
                    
                            if (allSelected) {
                                this.selectedVariant = this.availableVariants.find(variant => {
                                    return requiredAttributes.every(attrName => {
                                        const variantValue = variant.attribute_values.find(av =>
                                            av.attribute.name === attrName
                                        )?.value;
                                        return variantValue === this.selectedAttributes[attrName];
                                    });
                                });
                            } else {
                                this.selectedVariant = null;
                            }
                        },
                    
                        isAttributeValueAvailable(attributeName, value) {
                            const otherSelectedAttrs = Object.keys(this.selectedAttributes)
                                .filter(key => key !== attributeName)
                                .reduce((obj, key) => {
                                    obj[key] = this.selectedAttributes[key];
                                    return obj;
                                }, {});
                    
                            return this.availableVariants.some(variant => {
                                const hasThisValue = variant.attribute_values.some(av =>
                                    av.attribute.name === attributeName && av.value === value
                                );
                    
                                if (!hasThisValue) return false;
                    
                                const matchesOthers = Object.keys(otherSelectedAttrs).every(otherAttr => {
                                    const variantValue = variant.attribute_values.find(av =>
                                        av.attribute.name === otherAttr
                                    )?.value;
                                    return variantValue === otherSelectedAttrs[otherAttr];
                                });
                    
                                return matchesOthers && variant.inventory_quantity > 0;
                            });
                        },
                    
                        getCurrentPrice() {
                            if (this.selectedVariant) {
                                return '$' + parseFloat(this.selectedVariant.price).toFixed(2);
                            }
                            return '${{ number_format($product->price, 2) }}';
                        },
                    
                        getComparePrice() {
                            if (this.selectedVariant && this.selectedVariant.compare_price) {
                                return '$' + parseFloat(this.selectedVariant.compare_price).toFixed(2);
                            }
                            @if($product->compare_price)
                            return '${{ number_format($product->compare_price, 2) }}';
                            @endif
                            return null;
                        },
                    
                        getDiscountPercentage() {
                            if (this.selectedVariant && this.selectedVariant.compare_price && this.selectedVariant.compare_price > this.selectedVariant.price) {
                                return Math.round(((this.selectedVariant.compare_price - this.selectedVariant.price) / this.selectedVariant.compare_price) * 100);
                            }
                            @if($product->getDiscountPercentage())
                            return {{ $product->getDiscountPercentage() }};
                            @endif
                            return null;
                        },
                    
                        getStockMessage() {
                            if (!this.selectedVariant) return '';
                            const stock = this.selectedVariant.inventory_quantity;
                            if (stock <= 0) return 'Out of Stock';
                            if (stock <= this.lowStockThreshold) return 'Only a few left!';
                            return 'In Stock';
                        },
                    
                        getStockColor() {
                            if (!this.selectedVariant) return 'text-gray-600';
                            const stock = this.selectedVariant.inventory_quantity;
                            if (stock <= 0) return 'text-red-600';
                            if (stock <= this.lowStockThreshold) return 'text-orange-600';
                            return 'text-green-600';
                        },
                    
                        allRequiredAttributesSelected() {
                            return Object.keys(this.attributes).every(attr =>
                                this.selectedAttributes[attr]
                            );
                        }
                    }">

                        <!-- Dynamic Pricing -->
                        <div class="mb-6">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="text-3xl font-bold text-gray-900" x-text="getCurrentPrice()"></span>
                                <span x-show="getComparePrice()" x-text="getComparePrice()"
                                    class="text-xl text-gray-500 line-through"></span>
                                <span x-show="getDiscountPercentage()" x-text="'Save ' + getDiscountPercentage() + '%'"
                                    class="text-sm bg-red-100 text-red-800 px-2 py-1 rounded font-medium"></span>
                            </div>

                            <!-- Stock Status - Only show when variant is selected -->
                            <div x-show="selectedVariant" class="flex items-center" :class="getStockColor()">
                                <svg x-show="selectedVariant && selectedVariant.inventory_quantity > 0" class="w-5 h-5 mr-2"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="selectedVariant && selectedVariant.inventory_quantity <= 0"
                                    class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium" x-text="getStockMessage()"></span>
                            </div>
                        </div>

                        <!-- Completely Dynamic Attribute Selection -->
                        <template x-for="(attributeData, attributeName) in attributes" :key="attributeName">
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-900 mb-3">
                                    <span x-text="attributeName"></span>:
                                    <span
                                        x-text="selectedAttributes[attributeName] || 'Select ' + attributeName.toLowerCase()"
                                        class="font-normal"></span>
                                </h3>

                                <!-- Color Swatch Display (data-driven) -->
                                <div x-show="attributeData.display_type === 'color_swatch'" class="flex flex-wrap gap-3">
                                    <template x-for="valueData in attributeData.values" :key="valueData.value">
                                        <button @click="selectAttribute(attributeName, valueData.value)"
                                            :disabled="!isAttributeValueAvailable(attributeName, valueData.value)"
                                            :class="{
                                                'ring-2 ring-blue-500': selectedAttributes[attributeName] === valueData
                                                    .value,
                                                'opacity-25 cursor-not-allowed': !isAttributeValueAvailable(
                                                    attributeName, valueData.value),
                                                'hover:ring-1 hover:ring-gray-400': isAttributeValueAvailable(
                                                    attributeName, valueData.value) && selectedAttributes[
                                                    attributeName] !== valueData.value
                                            }"
                                            class="relative w-8 h-8 rounded-full border border-gray-300 focus:outline-none transition-all"
                                            :style="'background-color: ' + (valueData.color_code || '#CCCCCC')"
                                            :title="valueData.label">
                                            <!-- Not available overlay -->
                                            <div x-show="!isAttributeValueAvailable(attributeName, valueData.value)"
                                                class="absolute inset-0 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>

                                            <!-- Selected indicator -->
                                            <div x-show="selectedAttributes[attributeName] === valueData.value && isAttributeValueAvailable(attributeName, valueData.value)"
                                                class="absolute inset-0 flex items-center justify-center">
                                                <svg class="w-4 h-4"
                                                    :class="['White', 'Silver', 'Starlight', 'White Titanium'].includes(
                                                        valueData.value) ? 'text-gray-800' : 'text-white'"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                <!-- Button Display (data-driven) -->
                                <div x-show="attributeData.display_type === 'button'"
                                    :class="attributeData.values.length <= 4 ? 'grid grid-cols-2 sm:grid-cols-4 gap-3' :
                                        'grid grid-cols-3 sm:grid-cols-5 gap-2'">
                                    <template x-for="valueData in attributeData.values" :key="valueData.value">
                                        <button @click="selectAttribute(attributeName, valueData.value)"
                                            :disabled="!isAttributeValueAvailable(attributeName, valueData.value)"
                                            :class="{
                                                'border-blue-500 bg-blue-50 text-blue-600': selectedAttributes[
                                                    attributeName] === valueData.value,
                                                'border-gray-300 text-gray-900 hover:border-gray-400': selectedAttributes[
                                                    attributeName] !== valueData.value && isAttributeValueAvailable(
                                                    attributeName, valueData.value),
                                                'border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50': !
                                                    isAttributeValueAvailable(attributeName, valueData.value)
                                            }"
                                            class="relative py-3 px-4 border rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                            x-text="valueData.label">
                                            <!-- Not available overlay -->
                                            <div x-show="!isAttributeValueAvailable(attributeName, valueData.value)"
                                                class="absolute inset-0 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Add to Cart Button -->
                        <div class="mb-6">
                            <button
                                @click="if(selectedVariant && selectedVariant.inventory_quantity > 0) {
                addToCart({{ $product->id }}, selectedVariant.id);
            } else if(!allRequiredAttributesSelected()) {
                alert('Please select all options first');
            } else {
                alert('This variant is out of stock');
            }"
                                :disabled="!selectedVariant || selectedVariant.inventory_quantity <= 0"
                                :class="{
                                    'bg-blue-600 hover:bg-blue-700 text-white': selectedVariant && selectedVariant
                                        .inventory_quantity > 0,
                                    'bg-gray-400 text-white cursor-not-allowed': !selectedVariant || selectedVariant
                                        .inventory_quantity <= 0
                                }"
                                class="w-full py-3 px-6 rounded-lg font-medium focus:ring-2 focus:ring-blue-500 transition-colors">
                                <span x-show="!allRequiredAttributesSelected()">Select Options</span>
                                <span
                                    x-show="allRequiredAttributesSelected() && (!selectedVariant || selectedVariant.inventory_quantity <= 0)">Out
                                    of Stock</span>
                                <span x-show="selectedVariant && selectedVariant.inventory_quantity > 0">Add to Cart</span>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Simple Product -->
                    <div class="mb-6">
                        <!-- Price -->
                        <div class="flex items-center space-x-3 mb-4">
                            <span class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>

                            @if ($product->compare_price && $product->compare_price > $product->price)
                                <span
                                    class="text-xl text-gray-500 line-through">${{ number_format($product->compare_price, 2) }}</span>
                                <span class="text-sm bg-red-100 text-red-800 px-2 py-1 rounded font-medium">
                                    Save {{ $product->getDiscountPercentage() }}%
                                </span>
                            @endif
                        </div>

                        <!-- Stock Status -->
                        @if ($product->isInStock())
                            <div class="flex items-center text-green-600 mb-6">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">
                                    @if ($product->track_inventory && $product->inventory_quantity <= $product->low_stock_threshold)
                                        Only a few left!
                                    @else
                                        In Stock
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="flex items-center text-red-600 mb-6">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">Out of Stock</span>
                            </div>
                        @endif

                        <!-- Quantity & Add to Cart -->
                        <div class="flex items-center space-x-4">
                            <!-- Quantity Selector -->
                            <div class="flex items-center border border-gray-300 rounded">
                                <button onclick="decreaseQuantity()" class="px-3 py-2 hover:bg-gray-100">-</button>
                                <input type="number" id="quantity" value="1" min="1"
                                    class="w-16 px-3 py-2 text-center border-0 focus:ring-0">
                                <button onclick="increaseQuantity()" class="px-3 py-2 hover:bg-gray-100">+</button>
                            </div>

                            <!-- Add to Cart -->
                            @if ($product->isInStock())
                                <button onclick="addToCartWithQuantity({{ $product->id }})"
                                    class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                                    Add to Cart
                                </button>
                            @else
                                <button disabled
                                    class="flex-1 bg-gray-400 text-white py-3 px-6 rounded-lg font-medium cursor-not-allowed">
                                    Out of Stock
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Product Details -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Description</h3>
                        <div class="prose prose-sm text-gray-600">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Product Information</h3>
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">SKU</dt>
                                <dd class="text-sm text-gray-900">{{ $product->sku }}</dd>
                            </div>

                            @if ($product->weight)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Weight</dt>
                                    <dd class="text-sm text-gray-900">{{ $product->weight }} lbs</dd>
                                </div>
                            @endif

                            @if ($product->categories->count() > 0)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Categories</dt>
                                    <dd class="text-sm text-gray-900">
                                        @foreach ($product->categories as $category)
                                            <a href="{{ route('categories.show', $category->slug) }}"
                                                class="text-blue-600 hover:text-blue-700">
                                                {{ $category->name }}
                                            </a>{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </dd>
                                </div>
                            @endif

                            @if ($product->tags && count($product->tags) > 0)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Tags</dt>
                                    <dd class="text-sm text-gray-900">
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach ($product->tags as $tag)
                                                <span
                                                    class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if ($relatedProducts->count() > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('frontend.products.card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        // Image gallery functionality
        function changeMainImage(imageSrc) {
            document.getElementById('main-product-image').src = imageSrc;
        }

        // Quantity controls for simple products
        function increaseQuantity() {
            const qty = document.getElementById('quantity');
            qty.value = parseInt(qty.value) + 1;
        }

        function decreaseQuantity() {
            const qty = document.getElementById('quantity');
            if (parseInt(qty.value) > 1) {
                qty.value = parseInt(qty.value) - 1;
            }
        }

        // Add to cart with quantity
        function addToCartWithQuantity(productId) {
            const quantity = parseInt(document.getElementById('quantity').value);

            fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: null,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('cart-count').textContent = data.cart_count;
                        showToast(`Added ${quantity} item(s) to cart!`, 'success');
                    } else {
                        showToast('Error adding item to cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error adding item to cart', 'error');
                });
        }
    </script>
@endsection
