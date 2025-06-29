<?php

// app/Http/Controllers/Frontend/CartController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View
    {
        $cart = $this->cartService->getCart();
        $total = $this->cartService->getTotal();

        return view('frontend.cart.index', compact('cart', 'total'));
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($request->variant_id) {
            $variant = ProductVariant::findOrFail($request->variant_id);
            $this->cartService->addItem($variant, $request->quantity);
            $item = $variant;
        } else {
            $product = Product::findOrFail($request->product_id);
            $this->cartService->addItem($product, $request->quantity);
            $item = $product;
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_count' => $this->cartService->getItemCount(),
            'cart_total' => $this->cartService->getTotal()->value,
        ]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $this->cartService->updateQuantity($key, $request->quantity);

        return response()->json([
            'success' => true,
            'cart_total' => $this->cartService->getTotal()->value,
        ]);
    }

    public function remove(string $key): JsonResponse
    {
        $this->cartService->removeItem($key);

        return response()->json([
            'success' => true,
            'cart_count' => $this->cartService->getItemCount(),
            'cart_total' => $this->cartService->getTotal()->value,
        ]);
    }
}
