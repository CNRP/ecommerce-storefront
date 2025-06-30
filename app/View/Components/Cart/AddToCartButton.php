<?php

namespace App\View\Components\Cart;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\View\Component;

class AddToCartButton extends Component
{
    public function __construct(
        public Product|ProductVariant $item,
        public int $quantity = 1,
        public string $size = 'md',
        public bool $showQuantitySelector = false,
        public string $variant = 'primary'
    ) {}

    public function canAddToCart(): bool
    {
        if ($this->item instanceof ProductVariant) {
            return $this->item->is_active &&
                   $this->item->product->status === 'published' &&
                   $this->item->inventory_quantity > 0;
        }

        return $this->item->status === 'published' && $this->item->isInStock();
    }

    public function getItemId(): int
    {
        return $this->item->id;
    }

    public function getProductId(): int
    {
        return $this->item instanceof ProductVariant
            ? $this->item->product_id
            : $this->item->id;
    }

    public function getVariantId(): ?int
    {
        return $this->item instanceof ProductVariant ? $this->item->id : null;
    }

    public function getButtonClasses(): string
    {
        $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

        $sizeClasses = match ($this->size) {
            'sm' => 'px-3 py-1 text-sm',
            'lg' => 'px-6 py-3 text-lg',
            default => 'px-4 py-2 text-base'
        };

        $variantClasses = match ($this->variant) {
            'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
            'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
            default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
        };

        return trim($baseClasses.' '.$sizeClasses.' '.$variantClasses);
    }

    public function render()
    {
        return view('components.cart.add-to-cart-button');
    }
}
