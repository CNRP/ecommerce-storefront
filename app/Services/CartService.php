<?php

// app/Services/CartService.php

namespace App\Services;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\ValueObjects\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'shopping_cart';

    public function addItem(Product|ProductVariant $item, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $key = $this->getItemKey($item);

        // Convert collection to array for modification
        $cartArray = $cart->toArray();

        if (isset($cartArray[$key])) {
            $cartArray[$key]['quantity'] += $quantity;
        } else {
            // Determine if this is a product or variant
            if ($item instanceof ProductVariant) {
                $product = $item->product;
                $cartArray[$key] = [
                    'type' => 'variant',
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'variant_id' => $item->id, // Explicitly set variant_id
                    'name' => $item->getDisplayName(),
                    'price' => $item->price,
                    'quantity' => $quantity,
                    'image' => $item->image ?? $product->image,
                    'sku' => $item->sku,
                ];
            } else {
                // Simple product - FIXED: Make sure variant_id is properly null
                $cartArray[$key] = [
                    'type' => 'product',
                    'id' => $item->id,
                    'product_id' => $item->id,
                    'variant_id' => null, // IMPORTANT: Explicitly set to null
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $quantity,
                    'image' => $item->image,
                    'sku' => $item->sku,
                ];
            }
        }

        // Save the modified array back to session
        Session::put($this->sessionKey, $cartArray);

        Log::info('Item added to cart', [
            'key' => $key,
            'item_type' => $item instanceof ProductVariant ? 'variant' : 'product',
            'item_id' => $item->id,
            'product_id' => $item instanceof ProductVariant ? $item->product_id : $item->id,
            'variant_id' => $item instanceof ProductVariant ? $item->id : null, // Log this for debugging
            'quantity' => $quantity,
            'cart_total_items' => $this->getItemCount(),
        ]);
    }

    public function removeItem(string $key): void
    {
        $cartArray = $this->getCart()->toArray();
        unset($cartArray[$key]);
        Session::put($this->sessionKey, $cartArray);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($key);

            return;
        }

        $cartArray = $this->getCart()->toArray();
        if (isset($cartArray[$key])) {
            $cartArray[$key]['quantity'] = $quantity;
            Session::put($this->sessionKey, $cartArray);
        }
    }

    public function getCart(): Collection
    {
        $cart = collect(Session::get($this->sessionKey, []));

        // Ensure all cart items have the required fields
        return $cart->map(function ($item, $key) {
            // Ensure backward compatibility and proper structure
            return array_merge([
                'type' => 'product',
                'id' => null,
                'product_id' => null,
                'variant_id' => null,
                'name' => 'Unknown Item',
                'price' => 0,
                'quantity' => 1,
                'image' => null,
                'sku' => null,
            ], $item);
        });
    }

    public function getTotal(): Money
    {
        $total = $this->getCart()->sum(fn ($item) => $item['price'] * $item['quantity']);

        return new Money($total);
    }

    public function getItemCount(): int
    {
        return $this->getCart()->sum('quantity');
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    private function getItemKey(Product|ProductVariant $item): string
    {
        return $item instanceof Product ? "product_{$item->id}" : "variant_{$item->id}";
    }

    /**
     * Validate cart items and remove any that are no longer valid
     */
    public function validateAndCleanCart(): array
    {
        $cart = $this->getCart();
        $removedItems = [];
        $validCart = [];

        foreach ($cart as $key => $item) {
            try {
                if (isset($item['variant_id']) && $item['variant_id']) {
                    // Validate variant
                    $variant = ProductVariant::with('product')->find($item['variant_id']);
                    if (! $variant || ! $variant->is_active || $variant->product->status !== 'published') {
                        $removedItems[] = $item;

                        continue;
                    }
                } else {
                    // Validate simple product
                    $product = Product::find($item['product_id']);
                    if (! $product || $product->status !== 'published') {
                        $removedItems[] = $item;

                        continue;
                    }
                }

                $validCart[$key] = $item;
            } catch (\Exception $e) {
                Log::warning('Error validating cart item', [
                    'item' => $item,
                    'error' => $e->getMessage(),
                ]);
                $removedItems[] = $item;
            }
        }

        // Update cart with only valid items
        Session::put($this->sessionKey, $validCart);

        return $removedItems;
    }
}
