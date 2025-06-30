{{-- resources/views/components/navigation/cart.blade.php --}}
<button @click="cartOpen = true"
    class="relative p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
    <!-- Shopping Cart SVG -->
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-basket3-fill"
        viewBox="0 0 16 16">
        <path
            d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H.5a.5.5 0 0 1-.5-.5v-1A.5.5 0 0 1 .5 6h1.717L5.07 1.243a.5.5 0 0 1 .686-.172zM2.468 15.426.943 9h14.114l-1.525 6.426a.75.75 0 0 1-.729.574H3.197a.75.75 0 0 1-.73-.574z" />
    </svg>

    <!-- Cart Counter Badge -->
    <span id="cart-count"
        class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-medium rounded-full h-5 w-5 flex items-center justify-center transform translate-x-1 -translate-y-1">
        {{ app(\App\Services\CartService::class)->getItemCount() }}
    </span>
</button>
