{{-- resources/views/frontend/home.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Home - ' . config('app.name'))

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Welcome to {{ config('app.name') }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    Discover amazing products at unbeatable prices
                </p>
                <a href="{{ route('products.index') }}"
                    class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Shop Now
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    @if ($categories->count() > 0)
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Shop by Category</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    @foreach ($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}" class="group text-center">
                            <div
                                class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                @if ($category->image)
                                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}"
                                        class="w-12 h-12 object-cover rounded-full">
                                @else
                                    <svg class="w-8 h-8 text-gray-600 group-hover:text-blue-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                        </path>
                                    </svg>
                                @endif
                            </div>
                            <h3 class="font-medium text-gray-900 group-hover:text-blue-600">{{ $category->name }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Featured Products -->
    @if ($featuredProducts->count() > 0)
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900">Featured Products</h2>
                    <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">View All
                        →</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($featuredProducts as $product)
                        @include('frontend.products.card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- New Products -->
    @if ($newProducts->count() > 0)
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900">New Arrivals</h2>
                    <a href="{{ route('products.index', ['sort' => 'created_at']) }}"
                        class="text-blue-600 hover:text-blue-700 font-medium">View All →</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($newProducts as $product)
                        @include('frontend.products.card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Call to Action -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Shopping?</h2>
            <p class="text-xl mb-8 text-blue-100">Browse our collection of quality products</p>
            <a href="{{ route('products.index') }}"
                class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Explore Products
            </a>
        </div>
    </section>
@endsection
