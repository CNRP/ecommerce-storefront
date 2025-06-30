<?php

namespace App\View\Components\Cart;

use App\Services\CartService;
use Illuminate\View\Component;

class CartSidebar extends Component
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function getCartData(): array
    {
        return [
            'items' => $this->cartService->getCart(),
            'total' => $this->cartService->getTotal(),
            'count' => $this->cartService->getItemCount(),
        ];
    }

    public function render()
    {
        return view('components.cart.cart-sidebar');
    }
}
