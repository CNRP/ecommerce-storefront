{{-- resources/views/frontend/products/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Products - ' . config('app.name'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Products</h1>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <!-- Search -->
                    <div class="flex-1 min-w-64">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Price</label>
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="0"
                            class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Price</label>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="1000"
                            class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort by</label>
                        <select name="sort"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest
                            </option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to
                                High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High
                                to Low</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A to Z</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Count -->
        <div class="mb-6">
            <p class="text-gray-600">Showing {{ $products->count() }} of {{ $products->total() }} products</p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @forelse($products as $product)
                @include('frontend.products.card', ['product' => $product])
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No products found.</p>
                    <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">View
                        all products</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
