<?php

// app/Models/Order/OrderItem.php

namespace App\Models\Order;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'product_description',
        'variant_name',
        'variant_sku',
        'variant_attributes',
        'unit_price',
        'unit_cost',
        'compare_price',
        'quantity',
        'quantity_fulfilled',
        'quantity_cancelled',
        'quantity_refunded',
        'line_total',
        'tax_amount',
        'product_image',
        'fulfillment_details',
        'fulfilled_at',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'unit_cost' => 'integer',
        'compare_price' => 'integer',
        'quantity' => 'integer',
        'quantity_fulfilled' => 'integer',
        'quantity_cancelled' => 'integer',
        'quantity_refunded' => 'integer',
        'line_total' => 'integer',
        'tax_amount' => 'integer',
        'variant_attributes' => 'array',
        'fulfillment_details' => 'array',
        'fulfilled_at' => 'datetime',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // Money value objects
    public function getUnitPriceMoney(): Money
    {
        return Money::fromCents($this->unit_price, $this->order->currency);
    }

    public function getLineTotalMoney(): Money
    {
        return Money::fromCents($this->line_total, $this->order->currency);
    }

    public function getTaxMoney(): Money
    {
        return Money::fromCents($this->tax_amount, $this->order->currency);
    }

    // Helper methods
    public function getDisplayName(): string
    {
        return $this->variant_name ?: $this->product_name;
    }

    public function getSku(): string
    {
        return $this->variant_sku ?: $this->product_sku;
    }

    public function getQuantityPending(): int
    {
        return $this->quantity - $this->quantity_fulfilled - $this->quantity_cancelled;
    }

    public function canBeFulfilled(): bool
    {
        return $this->getQuantityPending() > 0;
    }

    public function getDiscountPercentage(): ?float
    {
        if (! $this->compare_price || $this->compare_price <= $this->unit_price) {
            return null;
        }

        return round((($this->compare_price - $this->unit_price) / $this->compare_price) * 100);
    }

    public static function createFromCartItem(array $cartItem, Order $order): self
    {
        // Load the actual product/variant to get current data
        $product = Product::find($cartItem['product_id']);
        $variant = isset($cartItem['variant_id']) ? ProductVariant::find($cartItem['variant_id']) : null;

        $item = $variant ?: $product;

        return self::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_description' => $product->short_description,
            'variant_name' => $variant?->getDisplayName(),
            'variant_sku' => $variant?->sku,
            'variant_attributes' => $variant?->attributeValues?->mapWithKeys(fn ($av) => [
                $av->attribute->name => $av->display_label,
            ])->toArray(),
            'unit_price' => (int) ($item->price * 100), // Convert to pence
            'unit_cost' => $item->cost_price ? (int) ($item->cost_price * 100) : null,
            'compare_price' => $item->compare_price ? (int) ($item->compare_price * 100) : null,
            'quantity' => $cartItem['quantity'],
            'line_total' => (int) ($item->price * $cartItem['quantity'] * 100),
            'tax_amount' => $order->tax_inclusive ?
                (int) ($item->price * $cartItem['quantity'] * 100 * $order->tax_rate / (1 + $order->tax_rate)) :
                (int) ($item->price * $cartItem['quantity'] * 100 * $order->tax_rate),
            'product_image' => $item->image ?? $product->image,
        ]);
    }
}
