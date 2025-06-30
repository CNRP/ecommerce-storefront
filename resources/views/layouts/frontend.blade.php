{{-- resources/views/layouts/frontend.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'E-commerce Store'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Custom Styles -->
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

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>

    <!-- Additional Head Content -->
    @stack('head')
</head>

<body class="bg-gray-50 min-h-screen flex flex-col" x-data="{ cartOpen: false, mobileMenuOpen: false }">
    <!-- Toast Notification System -->
    <div id="toast"
        class="fixed bottom-4 left-1/2 transform -translate-x-1/2 translate-y-32 z-50 transition-all duration-300 ease-in-out">
        <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-max">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span id="toast-message">Success!</span>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Navigation -->
    @include('components.navigation.main')

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Cart Sidebar -->
    @include('frontend.cart.sidebar')

    <!-- JavaScript -->
    <script>
        // Global JavaScript Configuration
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            routes: {
                cartAdd: '{{ route('cart.add') }}',
                cartUpdate: '{{ route('cart.update', ':key') }}',
                cartRemove: '{{ route('cart.remove', ':key') }}'
            }
        };

        // Toast Notification System
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastContainer = toast.querySelector('div');

            // Update message
            toastMessage.textContent = message;

            // Update styling based on type
            const baseClasses = 'px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-max';

            switch (type) {
                case 'success':
                    toastContainer.className = `bg-green-500 text-white ${baseClasses}`;
                    break;
                case 'error':
                    toastContainer.className = `bg-red-500 text-white ${baseClasses}`;
                    break;
                case 'warning':
                    toastContainer.className = `bg-yellow-500 text-white ${baseClasses}`;
                    break;
                case 'info':
                    toastContainer.className = `bg-blue-500 text-white ${baseClasses}`;
                    break;
                default:
                    toastContainer.className = `bg-gray-500 text-white ${baseClasses}`;
            }

            // Show toast
            toast.classList.remove('translate-y-32');
            toast.classList.add('translate-y-0');

            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.remove('translate-y-0');
                toast.classList.add('translate-y-32');
            }, 3000);
        }

        // Enhanced Add to Cart Function
        function addToCart(productId, variantId = null, quantity = 1) {
            const button = event?.target;
            let originalText = '';

            if (button) {
                originalText = button.textContent;
                button.textContent = 'Adding...';
                button.disabled = true;
                button.classList.add('opacity-75');
            }

            console.log('Adding to cart:', {
                productId,
                variantId,
                quantity
            });

            fetch(window.Laravel.routes.cartAdd, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.Laravel.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: quantity
                    })
                })
                .then(response => {
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

                        // Show success message
                        showToast(`Added ${quantity} item${quantity > 1 ? 's' : ''} to cart!`, 'success');
                    } else {
                        throw new Error(data.message || 'Failed to add item to cart');
                    }
                })
                .catch(error => {
                    console.error('Cart Error:', error);
                    showToast('Error adding item to cart', 'error');
                })
                .finally(() => {
                    // Reset button state
                    if (button) {
                        button.textContent = originalText;
                        button.disabled = false;
                        button.classList.remove('opacity-75');
                    }
                });
        }

        // Update Cart Quantity
        function updateCartQuantity(key, quantity) {
            if (quantity < 1) {
                removeFromCart(key);
                return;
            }

            const url = window.Laravel.routes.cartUpdate.replace(':key', key);

            fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.Laravel.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count and total
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }

                        // Reload page to update cart display
                        location.reload();
                    } else {
                        showToast('Error updating cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Update cart error:', error);
                    showToast('Error updating cart', 'error');
                });
        }

        // Remove from Cart
        function removeFromCart(key) {
            const url = window.Laravel.routes.cartRemove.replace(':key', key);

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': window.Laravel.csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }

                        showToast('Item removed from cart', 'info');

                        // Reload page to update cart display
                        location.reload();
                    } else {
                        showToast('Error removing item', 'error');
                    }
                })
                .catch(error => {
                    console.error('Remove from cart error:', error);
                    showToast('Error removing item', 'error');
                });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Frontend layout loaded successfully');

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                const mobileMenu = document.querySelector('[x-data]').__x;
                if (mobileMenu && mobileMenu.$data.mobileMenuOpen) {
                    const nav = document.querySelector('header nav');
                    if (!nav.contains(e.target)) {
                        mobileMenu.$data.mobileMenuOpen = false;
                    }
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                // Close cart with Escape key
                if (e.key === 'Escape') {
                    const appData = document.querySelector('[x-data]').__x;
                    if (appData) {
                        appData.$data.cartOpen = false;
                        appData.$data.mobileMenuOpen = false;
                    }
                }
            });
        });

        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = window.performance.timing.domContentLoadedEventEnd - window.performance.timing
                .navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>

</html>
