<?php

// app/Http/Controllers/Frontend/CategoryController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category, Request $request): View
    {
        $query = $category->products()
            ->with(['categories', 'variants'])
            ->published()
            ->inStock();

        // Search within category
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $subcategories = $category->children()->active()->get();

        return view('frontend.categories.show', compact('category', 'products', 'subcategories'));
    }
}
