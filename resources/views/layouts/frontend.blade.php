{{-- resources/views/layouts/frontend.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'E-commerce Store'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-gray-50" x-data="{ cartOpen: false }">
    <!-- Toast Notification - Bottom Center - Fixed Hidden State -->
    <div id="toast"
        class="fixed bottom-4 left-1/2 transform -translate-x-1/2 translate-y-32 z-50 transition-all duration-300 ease-in-out">
        <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-max">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span id="toast-message">Item added to cart!</span>
        </div>
    </div>

    <!-- Header - Completely Fixed -->
    <header class="bg-white shadow-sm border-b border-gray-300 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}"
                        class="text-2xl font-bold text-gray-900 hover:text-gray-700 transition-colors">
                        {{ config('app.name', 'Store') }}
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}"
                        class="text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('home') ? 'text-blue-600 font-medium' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('products.index') }}"
                        class="text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('products.*') ? 'text-blue-600 font-medium' : '' }}">
                        Products
                    </a>

                </nav>

                <!-- Search Bar -->
                <div class="flex-1 max-w-lg mx-4 lg:mx-8">
                    <form action="{{ route('products.index') }}" method="GET" class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search products..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors">
                        <button type="submit"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Cart Icon - Fixed positioning -->
                <div class="flex-shrink-0">
                    <button @click="cartOpen = true"
                        class="relative p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                        <!-- Proper Shopping Cart SVG -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 7H6L5 9z"></path>
                        </svg>

                        <!-- Cart Counter Badge - Better positioning -->
                        <span id="cart-count"
                            class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-medium rounded-full h-5 w-5 flex items-center justify-center transform translate-x-1 -translate-y-1">
                            {{ app(\App\Services\CartService::class)->getItemCount() }}
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ config('app.name') }}</h3>
                    <p class="text-gray-300">Your trusted online store for quality products.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="{{ route('products.index') }}"
                                class="hover:text-white transition-colors">Products</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Categories</h3>
                    <ul class="space-y-2 text-gray-300">
                        @foreach (\App\Models\Product\Category::active()->root()->limit(5)->get() as $category)
                            <li><a href="{{ route('categories.show', $category->slug) }}"
                                    class="hover:text-white transition-colors">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <p class="text-gray-300">Email: info@example.com</p>
                    <p class="text-gray-300">Phone: (555) 123-4567</p>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Cart Sidebar -->
    @include('frontend.cart.sidebar')

    <!-- JavaScript -->
    <script>
        // CSRF token setup
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Toast notification system - Fixed for complete hiding
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastContainer = toast.querySelector('div');

            // Update message
            toastMessage.textContent = message;

            // Update styling based on type
            if (type === 'success') {
                toastContainer.className =
                    'bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-max';
            } else if (type === 'error') {
                toastContainer.className =
                    'bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-max';
            } else {
                toastContainer.className =
                    'bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-max';
            }

            // Show toast - slide up from completely hidden
            toast.classList.remove('translate-y-32');
            toast.classList.add('translate-y-0');

            // Hide toast after 3 seconds - move completely off screen
            setTimeout(() => {
                toast.classList.remove('translate-y-0');
                toast.classList.add('translate-y-32');
            }, 3000);
        }

        // Fixed add to cart function
        function addToCart(productId, variantId = null) {
            // Get the button that was clicked
            const button = event.target;
            const originalText = button.textContent;

            // Show loading state
            button.textContent = 'Adding...';
            button.disabled = true;
            button.classList.add('opacity-75');

            // Debug log
            console.log('Adding to cart:', {
                productId,
                variantId
            });

            // Make the API request
            fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: 1
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Cart response:', data);
                    if (data.success) {
                        // Update cart count
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;

                            // Animate cart icon
                            cartCount.classList.add('animate-bounce');
                            setTimeout(() => cartCount.classList.remove('animate-bounce'), 1000);
                        }

                        // Show success toast
                        showToast('Item added to cart!', 'success');

                    } else {
                        console.error('Cart operation failed:', data);
                        showToast(data.message || 'Failed to add item to cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Cart Error:', error);
                    showToast('Error adding item to cart', 'error');
                })
                .finally(() => {
                    // Reset button state
                    button.textContent = originalText;
                    button.disabled = false;
                    button.classList.remove('opacity-75');
                });
        }

        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Frontend layout loaded successfully');
        });
    </script>
</body>

</html>
