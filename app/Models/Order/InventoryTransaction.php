<?php

// app/Models/Order/InventoryTransaction.php

namespace App\Models\Order;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'order_id',
        'type',
        'quantity_change',
        'quantity_after',
        'reference',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'quantity_after' => 'integer',
        'metadata' => 'array',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Helper methods
    public function isPositive(): bool
    {
        return $this->quantity_change > 0;
    }

    public function isNegative(): bool
    {
        return $this->quantity_change < 0;
    }

    public function getTypeDescription(): string
    {
        return match ($this->type) {
            'sale' => 'Sale',
            'return' => 'Return',
            'restock' => 'Restock',
            'adjustment' => 'Manual Adjustment',
            'reservation' => 'Reserved for Order',
            'release' => 'Reservation Released',
            default => ucfirst($this->type),
        };
    }

    public static function recordTransaction(
        Product|ProductVariant $item,
        string $type,
        int $quantityChange,
        ?Order $order = null,
        ?string $reference = null,
        ?string $notes = null
    ): self {
        // FIXED: Get current inventory correctly for both products and variants
        $currentInventory = $item->inventory_quantity;
        $newInventory = $currentInventory + $quantityChange;

        // Update the item's inventory
        $item->update(['inventory_quantity' => $newInventory]);

        // FIXED: Create transaction record with correct IDs
        return self::create([
            'product_id' => $item instanceof Product ? $item->id : $item->product_id,
            'product_variant_id' => $item instanceof ProductVariant ? $item->id : null,
            'order_id' => $order?->id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'quantity_after' => $newInventory,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }
}
