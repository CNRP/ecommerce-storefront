<?php

// app/Http/Controllers/Frontend/HomeController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::published()
            ->inStock()
            ->featured()
            ->limit(8)
            ->get();

        $newProducts = Product::published()
            ->inStock()
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $categories = Category::active()
            ->root()
            ->limit(6)
            ->get();

        return view('frontend.home', compact('featuredProducts', 'newProducts', 'categories'));
    }
}
