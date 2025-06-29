<?php

// app/Services/OrderService.php

namespace App\Services;

use App\Models\Customer\Customer;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Create a new order from cart data - Updated to handle proper cart structure
     */
    public function createOrder(
        Customer $customer,
        Collection $cartItems,
        array $billingAddress,
        array $shippingAddress,
        array $orderData = []
    ): Order {
        return DB::transaction(function () use ($customer, $cartItems, $billingAddress, $shippingAddress, $orderData) {
            Log::info('Creating order', [
                'customer_id' => $customer->id,
                'cart_items_count' => $cartItems->count(),
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'order_data' => $orderData,
            ]);

            // Calculate totals
            $totals = $this->calculateOrderTotals($cartItems, $orderData);

            // Create the order
            $order = Order::create([
                'customer_id' => $customer->id,
                'vendor_id' => $orderData['vendor_id'] ?? null,
                'status' => 'pending_payment',
                'subtotal_amount' => $totals['subtotal_amount'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'discount_amount' => $totals['discount_amount'],
                'total_amount' => $totals['total_amount'],
                'currency' => $orderData['currency'] ?? 'GBP',
                'tax_rate' => $orderData['tax_rate'] ?? 0.20,
                'tax_inclusive' => $orderData['tax_inclusive'] ?? true,
                'customer_details' => [
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'customer_notes' => $orderData['customer_notes'] ?? null,
                'metadata' => $orderData['metadata'] ?? [],
            ]);

            Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // Create order items with proper validation
            foreach ($cartItems as $cartItem) {
                $this->createOrderItemFromCart($cartItem, $order);
            }

            // FIXED: Load relationships before reserving inventory
            $order->load(['items.product', 'items.productVariant']);

            // Reserve inventory
            $this->inventoryService->reserveInventoryForOrder($order);

            return $order;
        });
    }

    /**
     * Create an order item from cart data with proper validation - UPDATED
     */
    protected function createOrderItemFromCart(array $cartItem, Order $order): void
    {
        Log::info('Creating order item from cart', ['cart_item' => $cartItem, 'order_id' => $order->id]);

        // Validate required fields
        if (! isset($cartItem['product_id']) || ! $cartItem['product_id']) {
            throw new \InvalidArgumentException('Cart item missing product_id');
        }

        if (! isset($cartItem['quantity']) || $cartItem['quantity'] <= 0) {
            throw new \InvalidArgumentException('Cart item missing or invalid quantity');
        }

        // Load the actual product/variant to get current data
        $product = Product::with(['variants.attributeValues.attribute'])->find($cartItem['product_id']);

        if (! $product) {
            throw new \InvalidArgumentException("Product {$cartItem['product_id']} not found when creating order");
        }

        $variant = null;
        $item = $product; // Default to product for pricing

        // FIXED: Better variant handling
        // If there's a variant_id in cart AND it's not null AND the product has variants
        if (isset($cartItem['variant_id']) && $cartItem['variant_id'] && $product->hasVariants()) {
            $variant = $product->variants->firstWhere('id', $cartItem['variant_id']);
            if (! $variant) {
                throw new \InvalidArgumentException("Variant {$cartItem['variant_id']} not found when creating order");
            }
            $item = $variant; // Use variant for pricing
        } elseif ($product->hasVariants() && (! isset($cartItem['variant_id']) || ! $cartItem['variant_id'])) {
            // Product has variants but no variant selected - this shouldn't happen after cart validation
            throw new \InvalidArgumentException("Product {$product->name} requires a variant selection");
        }
        // If product doesn't have variants, we use the product itself (simple product)

        // Prepare variant attributes for storage
        $variantAttributes = null;
        if ($variant && $variant->attributeValues) {
            $variantAttributes = $variant->attributeValues->mapWithKeys(function ($attributeValue) {
                return [$attributeValue->attribute->name => $attributeValue->display_label];
            })->toArray();
        }

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_description' => $product->short_description,
            'variant_name' => $variant?->getDisplayName(),
            'variant_sku' => $variant?->sku,
            'variant_attributes' => $variantAttributes,
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

        Log::info('Order item created', [
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'is_simple_product' => ! $product->hasVariants(),
            'quantity' => $cartItem['quantity'],
            'unit_price' => $orderItem->unit_price,
            'line_total' => $orderItem->line_total,
        ]);
    }

    /**
     * Calculate order totals from cart items
     */
    protected function calculateOrderTotals(Collection $cartItems, array $orderData = []): array
    {
        $subtotalAmount = 0;
        $taxRate = $orderData['tax_rate'] ?? 0.20;
        $taxInclusive = $orderData['tax_inclusive'] ?? true;
        $shippingAmount = ($orderData['shipping_amount'] ?? 0) * 100; // Convert to pence
        $discountAmount = ($orderData['discount_amount'] ?? 0) * 100; // Convert to pence

        foreach ($cartItems as $item) {
            // Validate item has required fields
            if (! isset($item['price']) || ! isset($item['quantity'])) {
                throw new \InvalidArgumentException('Cart item missing price or quantity');
            }

            $lineTotal = $item['price'] * $item['quantity'];
            $subtotalAmount += $lineTotal * 100; // Convert to pence
        }

        // Calculate tax
        if ($taxInclusive) {
            // Tax is already included in prices
            $taxAmount = (int) ($subtotalAmount * $taxRate / (1 + $taxRate));
        } else {
            // Tax needs to be added
            $taxAmount = (int) ($subtotalAmount * $taxRate);
        }

        $totalAmount = $subtotalAmount + $shippingAmount - $discountAmount;
        if (! $taxInclusive) {
            $totalAmount += $taxAmount;
        }

        $totals = [
            'subtotal_amount' => $subtotalAmount,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];

        Log::info('Order totals calculated', $totals);

        return $totals;
    }

    /**
     * Update order status with proper validation and history tracking
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $notes = null, ?User $user = null): bool
    {
        if (! $order->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException("Cannot transition order {$order->order_number} from {$order->status} to {$newStatus}");
        }

        $updated = $order->updateStatus($newStatus, $notes, $user);

        if ($updated) {
            // Handle status-specific actions
            match ($newStatus) {
                'processing' => $this->handleProcessingStatus($order),
                'fulfilled' => $this->handleFulfilledStatus($order),
                'cancelled' => $this->handleCancelledStatus($order),
                'completed' => $this->handleCompletedStatus($order),
                default => null,
            };

            // TODO: Dispatch events for notifications
            // event(new OrderStatusUpdated($order, $newStatus));
        }

        return $updated;
    }

    /**
     * Cancel an order and handle inventory/payment
     */
    public function cancelOrder(Order $order, ?string $reason = null, ?User $user = null): bool
    {
        if (! $order->canBeCancelled()) {
            throw new \InvalidArgumentException("Order {$order->order_number} cannot be cancelled in its current state");
        }

        return DB::transaction(function () use ($order, $reason, $user) {
            // Release reserved inventory
            $this->inventoryService->releaseReservedInventory($order);

            // Handle payment cancellation if needed
            if ($order->isPaid()) {
                // TODO: Handle refund logic
                $this->paymentService->refundPayment($order->stripe_payment_intent_id, $order->total_amount);
            }

            // Update order status
            return $this->updateOrderStatus($order, 'cancelled', $reason, $user);
        });
    }

    /**
     * Mark order items as fulfilled
     */
    public function fulfillOrderItems(Order $order, array $fulfillmentData, ?User $user = null): bool
    {
        return DB::transaction(function () use ($order, $fulfillmentData, $user) {
            $allFulfilled = true;

            foreach ($fulfillmentData as $itemId => $data) {
                $orderItem = $order->items()->find($itemId);
                if (! $orderItem || ! $orderItem->canBeFulfilled()) {
                    continue;
                }

                $quantityToFulfill = min($data['quantity'], $orderItem->getQuantityPending());

                // Update order item
                $orderItem->update([
                    'quantity_fulfilled' => $orderItem->quantity_fulfilled + $quantityToFulfill,
                    'fulfillment_details' => array_merge(
                        $orderItem->fulfillment_details ?? [],
                        $data['details'] ?? []
                    ),
                    'fulfilled_at' => $orderItem->quantity_fulfilled + $quantityToFulfill >= $orderItem->quantity ? now() : $orderItem->fulfilled_at,
                ]);

                // Record inventory transaction
                $item = $orderItem->productVariant ?: $orderItem->product;
                $this->inventoryService->recordSale($item, $quantityToFulfill, $order);

                if ($orderItem->getQuantityPending() > 0) {
                    $allFulfilled = false;
                }
            }

            // Update order status based on fulfillment
            if ($allFulfilled) {
                $this->updateOrderStatus($order, 'fulfilled', 'All items fulfilled', $user);
            } elseif ($order->getTotalFulfilledItems() > 0) {
                $this->updateOrderStatus($order, 'partially_fulfilled', 'Partial fulfillment completed', $user);
            }

            return true;
        });
    }

    /**
     * Complete an order
     */
    public function completeOrder(Order $order, ?User $user = null): bool
    {
        if (! $order->isFullyFulfilled()) {
            throw new \InvalidArgumentException("Order {$order->order_number} must be fully fulfilled before completion");
        }

        return $this->updateOrderStatus($order, 'completed', 'Order completed', $user);
    }

    /**
     * Get orders for a customer with filtering
     */
    public function getCustomerOrders(Customer $customer, array $filters = []): Collection
    {
        $query = $customer->orders()->with(['items.product', 'items.productVariant']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // Protected helper methods for status handling
    protected function handleProcessingStatus(Order $order): void
    {
        // Order is now being processed, inventory should already be reserved
        // TODO: Send processing notification to customer
        Log::info('Order processing status handled', ['order_id' => $order->id]);
    }

    protected function handleFulfilledStatus(Order $order): void
    {
        // TODO: Send shipping notification to customer
        // TODO: Generate shipping labels if needed
        Log::info('Order fulfilled status handled', ['order_id' => $order->id]);
    }

    protected function handleCancelledStatus(Order $order): void
    {
        // TODO: Send cancellation notification to customer
        Log::info('Order cancelled status handled', ['order_id' => $order->id]);
    }

    protected function handleCompletedStatus(Order $order): void
    {
        // TODO: Send completion notification to customer
        // TODO: Request review/feedback
        Log::info('Order completed status handled', ['order_id' => $order->id]);
    }
}
