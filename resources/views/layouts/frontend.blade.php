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

<body class="bg-gray-50 min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false }">

    <!-- Success/Error Flash Messages -->
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

    <!-- Global Components -->
    <x-cart.cart-sidebar />
    <x-ui.toast-container />

    <!-- Load Cart Store JavaScript -->
    @vite(['resources/js/stores/cart.js'])

    <!-- Minimal Global JavaScript -->
    <!-- Complete Cart Store JavaScript (inline) -->
    <script>
        function toastContainer() {
            return {
                toasts: [],
                nextId: 1,

                addToast(detail) {
                    const toast = {
                        id: this.nextId++,
                        message: detail.message,
                        type: detail.type || 'info',
                        show: true
                    };

                    this.toasts.push(toast);

                    setTimeout(() => {
                        this.removeToast(toast.id);
                    }, 5000);
                },

                removeToast(id) {
                    const index = this.toasts.findIndex(toast => toast.id === id);
                    if (index > -1) {
                        this.toasts[index].show = false;
                        setTimeout(() => {
                            const newIndex = this.toasts.findIndex(toast => toast.id === id);
                            if (newIndex > -1) {
                                this.toasts.splice(newIndex, 1);
                            }
                        }, 300);
                    }
                }
            };
        }


        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = window.performance.timing.domContentLoadedEventEnd -
                window.performance.timing.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (window.Alpine && Alpine.store) {
                    const cartStore = Alpine.store('cart');
                    if (cartStore && cartStore.isOpen) {
                        cartStore.close();
                    }
                }

                const appData = document.querySelector('[x-data]').__x;
                if (appData && appData.$data.mobileMenuOpen) {
                    appData.$data.mobileMenuOpen = false;
                }
            }
        });

        // Global helper function (for backward compatibility)
        window.addToCart = async function(productId, variantId = null, quantity = 1) {
            if (window.Alpine && Alpine.store) {
                try {
                    await Alpine.store('cart').addItem(productId, variantId, quantity);
                } catch (error) {
                    console.error('Error adding to cart:', error);
                }
            }
        };

        console.log('Cart system initialized');
    </script>

    @stack('scripts')
</body>

</html>
