{{-- resources/views/components/navigation/main.blade.php --}}
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
                <a href="{{ route('orders.track') }}"
                    class="text-gray-700 hover:text-blue-600 transition-colors {{ request()->routeIs('orders.track') ? 'text-blue-600 font-medium' : '' }}">
                    Track Order
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

            <!-- Right Side: Auth + Cart -->
            <div class="flex items-center space-x-4">
                <!-- Authentication Section -->
                @include('components.navigation.auth')

                <!-- Cart Icon -->
                @include('components.navigation.cart')
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden ml-4">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 hover:text-gray-700 p-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1" class="md:hidden border-t border-gray-200 bg-white">

            <!-- Mobile Navigation Links -->
            <div class="px-4 py-2 space-y-1">
                <a href="{{ route('home') }}"
                    class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-600' : '' }}">
                    Home
                </a>
                <a href="{{ route('products.index') }}"
                    class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                    Products
                </a>
                <a href="{{ route('orders.track') }}"
                    class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md {{ request()->routeIs('orders.track') ? 'bg-blue-50 text-blue-600' : '' }}">
                    Track Order
                </a>
            </div>

            <!-- Mobile Authentication Section -->
            @include('components.navigation.auth-mobile')
        </div>
    </div>
</header>
