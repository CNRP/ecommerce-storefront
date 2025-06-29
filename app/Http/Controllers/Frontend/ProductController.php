<?php

// app/Http/Controllers/Frontend/ProductController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        // Use Scout search when there's a search term
        if ($request->filled('search')) {
            $query = Product::search($request->search);

            // Apply additional filters via Scout's query callback
            $query->query(function ($q) use ($request) {
                $q->with(['categories', 'variants', 'vendor'])
                    ->published()
                    ->inStock();

                // Category filter
                if ($request->filled('category')) {
                    $q->whereHas('categories', function ($subQ) use ($request) {
                        $subQ->where('slug', $request->category);
                    });
                }

                // Vendor filter
                if ($request->filled('vendor')) {
                    $q->whereHas('vendor', function ($subQ) use ($request) {
                        $subQ->where('slug', $request->vendor);
                    });
                }

                // Price filters
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }

                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }

                // Featured filter
                if ($request->filled('featured')) {
                    $q->featured();
                }

                // Sorting (for Scout results)
                $this->applySorting($q, $request);
            });

            $products = $query->paginate(12)->withQueryString();
        } else {
            // Use regular Eloquent when no search term
            $query = Product::with(['categories', 'variants', 'vendor'])
                ->published()
                ->inStock();

            // Category filter
            if ($request->filled('category')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            // Vendor filter
            if ($request->filled('vendor')) {
                $query->whereHas('vendor', function ($q) use ($request) {
                    $q->where('slug', $request->vendor);
                });
            }

            // Price filters
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Featured filter
            if ($request->filled('featured')) {
                $query->featured();
            }

            // Sorting
            $this->applySorting($query, $request);

            $products = $query->paginate(12)->withQueryString();
        }

        $categories = Category::active()->root()->with('children')->get();

        return view('frontend.products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->load(['categories', 'variants.attributeValues.attribute', 'vendor']);

        // Get related products with proper table prefixes
        $relatedProducts = Product::published()
            ->inStock()
            ->whereHas('categories', function ($q) use ($product) {
                $q->whereIn('categories.id', $product->categories->pluck('id'));
            })
            ->where('products.id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('frontend.products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Search suggestions for autocomplete
     */
    public function searchSuggestions(Request $request)
    {
        if (! $request->filled('q') || strlen($request->q) < 2) {
            return response()->json([]);
        }

        $suggestions = Product::search($request->q)
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'image' => $product->getMainImage(),
                    'url' => route('products.show', $product->slug),
                ];
            });

        return response()->json($suggestions);
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request): void
    {
        $sort = $request->get('sort', 'created_at');

        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            'popularity' => $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'featured' => $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc'),
            default => $query->orderBy('created_at', 'desc')
        };
    }
}
