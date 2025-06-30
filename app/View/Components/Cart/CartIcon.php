<?php

namespace App\View\Components\Cart;

use App\Services\CartService;
use Illuminate\View\Component;

class CartIcon extends Component
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function getCartCount(): int
    {
        return $this->cartService->getItemCount();
    }

    public function render()
    {
        return view('components.cart.cart-icon');
    }
}
