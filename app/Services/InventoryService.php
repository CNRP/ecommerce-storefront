<?php

// app/Services/InventoryService.php

namespace App\Services;

use App\Models\Order\InventoryTransaction;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Reserve inventory for an order - FIXED
     */
    public function reserveInventoryForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                // FIXED: Determine the item and whether to track inventory
                if ($orderItem->productVariant) {
                    // This is a variant order item
                    $item = $orderItem->productVariant;
                    $shouldTrackInventory = $orderItem->product->track_inventory;
                } else {
                    // This is a simple product order item
                    $item = $orderItem->product;
                    $shouldTrackInventory = $orderItem->product->track_inventory;
                }

                if ($shouldTrackInventory) {
                    $this->reserveInventory(
                        $item,
                        $orderItem->quantity,
                        $order,
                        "Reserved for order {$order->order_number}"
                    );
                }
            }
        });
    }

    /**
     * Release reserved inventory for an order - FIXED
     */
    public function releaseReservedInventory(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                // FIXED: Same logic for releasing
                if ($orderItem->productVariant) {
                    $item = $orderItem->productVariant;
                    $shouldTrackInventory = $orderItem->product->track_inventory;
                } else {
                    $item = $orderItem->product;
                    $shouldTrackInventory = $orderItem->product->track_inventory;
                }

                if ($shouldTrackInventory) {
                    $this->releaseInventory(
                        $item,
                        $orderItem->quantity,
                        $order,
                        "Released from cancelled order {$order->order_number}"
                    );
                }
            }
        });
    }

    /**
     * Record a sale (when item is actually fulfilled)
     */
    public function recordSale(Product|ProductVariant $item, int $quantity, Order $order): void
    {
        InventoryTransaction::recordTransaction(
            $item,
            'sale',
            -$quantity,
            $order,
            $order->order_number,
            "Sale for order {$order->order_number}"
        );
    }

    /**
     * Record a return
     */
    public function recordReturn(Product|ProductVariant $item, int $quantity, Order $order): void
    {
        InventoryTransaction::recordTransaction(
            $item,
            'return',
            $quantity,
            $order,
            $order->order_number,
            "Return for order {$order->order_number}"
        );
    }

    /**
     * Reserve inventory - PRIVATE METHOD FIX
     */
    protected function reserveInventory(Product|ProductVariant $item, int $quantity, Order $order, string $notes): void
    {
        InventoryTransaction::recordTransaction(
            $item,
            'reservation',
            -$quantity,
            $order,
            $order->order_number,
            $notes
        );
    }

    /**
     * Release reserved inventory - PRIVATE METHOD FIX
     */
    protected function releaseInventory(Product|ProductVariant $item, int $quantity, Order $order, string $notes): void
    {
        InventoryTransaction::recordTransaction(
            $item,
            'release',
            $quantity,
            $order,
            $order->order_number,
            $notes
        );
    }

    /**
     * Manual inventory adjustment
     */
    public function adjustInventory(Product|ProductVariant $item, int $newQuantity, string $reason): void
    {
        $currentQuantity = $item->inventory_quantity;
        $adjustment = $newQuantity - $currentQuantity;

        if ($adjustment !== 0) {
            InventoryTransaction::recordTransaction(
                $item,
                'adjustment',
                $adjustment,
                null,
                'Manual adjustment',
                $reason
            );
        }
    }

    /**
     * Restock inventory
     */
    public function restockInventory(Product|ProductVariant $item, int $quantity, ?string $reference = null): void
    {
        InventoryTransaction::recordTransaction(
            $item,
            'restock',
            $quantity,
            null,
            $reference,
            'Inventory restocked'
        );
    }

    /**
     * Get inventory history for an item
     */
    public function getInventoryHistory(Product|ProductVariant $item, int $limit = 50): \Illuminate\Support\Collection
    {
        $query = InventoryTransaction::with(['order'])
            ->where($item instanceof Product ? 'product_id' : 'product_variant_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        return $query->get();
    }

    /**
     * Check if sufficient stock is available
     */
    public function checkStockAvailability(Product|ProductVariant $item, int $requestedQuantity): bool
    {
        // For products, check if they track inventory
        if ($item instanceof Product) {
            if (! $item->track_inventory) {
                return true;
            }
        } else {
            // For variants, check if the parent product tracks inventory
            if (! $item->product->track_inventory) {
                return true;
            }
        }

        return $item->inventory_quantity >= $requestedQuantity;
    }
}
