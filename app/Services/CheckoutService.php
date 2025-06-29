<?php

// app/Services/CheckoutService.php

namespace App\Services;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerAddress;
use App\Models\Order\Order;
use App\Models\Product\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Initialize checkout process
     */
    public function initializeCheckout(array $checkoutData): array
    {
        $cart = $this->cartService->getCart();

        if ($cart->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        Log::info('Cart contents for validation', ['cart' => $cart->toArray()]);

        // Validate stock availability
        $this->validateCartStock($cart);

        // Find or create customer
        $customer = $this->findOrCreateCustomer($checkoutData['customer']);

        // Create or update addresses
        $billingAddress = $this->createOrUpdateAddress($customer, $checkoutData['billing_address'], 'billing');
        $shippingAddress = $this->createOrUpdateAddress($customer, $checkoutData['shipping_address'] ?? $checkoutData['billing_address'], 'shipping');

        // Create the order
        $order = $this->orderService->createOrder(
            $customer,
            $cart,
            $billingAddress->toArray(),
            $shippingAddress->toArray(),
            $checkoutData['order_data'] ?? []
        );

        // Create Stripe Payment Intent
        $paymentIntent = $this->paymentService->createPaymentIntent($order, $customer);

        return [
            'order' => $order,
            'customer' => $customer,
            'payment_intent' => $paymentIntent,
            'client_secret' => $paymentIntent['client_secret'],
        ];
    }

    /**
     * Complete checkout after successful payment
     */
    public function completeCheckout(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            // Update order status to processing
            $this->orderService->updateOrderStatus($order, 'processing', 'Payment confirmed');

            // Clear the cart
            $this->cartService->clear();

            return [
                'order' => $order->fresh(['items', 'customer']),
                'success' => true,
                'message' => 'Order placed successfully',
            ];
        });
    }

    /**
     * Handle checkout failure
     */
    public function handleCheckoutFailure(Order $order, string $reason): void
    {
        $this->orderService->updateOrderStatus($order, 'payment_failed', $reason);
    }

    protected function validateCartStock(\Illuminate\Support\Collection $cart): void
    {
        foreach ($cart as $key => $item) {
            Log::info('Validating cart item', ['key' => $key, 'item' => $item]);

            // Ensure we have a product_id
            if (! isset($item['product_id']) || ! $item['product_id']) {
                throw new \InvalidArgumentException("Cart item missing product_id: {$key}");
            }

            // Load the product with necessary relationships
            $product = Product::with(['variants'])->find($item['product_id']);

            if (! $product) {
                throw new \InvalidArgumentException("Product {$item['product_id']} no longer exists");
            }

            Log::info('Product found', [
                'product_id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'track_inventory' => $product->track_inventory,
                'inventory_quantity' => $product->inventory_quantity,
                'has_variants' => $product->hasVariants(),
                'variant_count' => $product->variants->count(),
            ]);

            if ($product->status !== 'published') {
                throw new \InvalidArgumentException("Product {$product->name} is no longer available");
            }

            // Determine stock validation strategy
            if (! $product->track_inventory) {
                Log::info('Product does not track inventory, skipping stock validation', ['product_id' => $product->id]);

                continue; // Skip stock validation for products that don't track inventory
            }

            // FIXED: Better logic for determining if this is a variant or simple product
            // Check if the cart item has a variant_id AND the product actually has variants
            if (isset($item['variant_id']) && $item['variant_id'] && $product->hasVariants()) {
                $this->validateVariantStock($product, $item);
            } else {
                // This is either a simple product or a product where no variant was selected
                $this->validateSimpleProductStock($product, $item);
            }
        }
    }

    /**
     * Validate stock for a simple product (no variants) - UPDATED
     */
    protected function validateSimpleProductStock(Product $product, array $item): void
    {
        // FIXED: Better validation logic for simple products
        // If the product has variants but no variant was selected in the cart, that's an error
        if ($product->hasVariants() && (! isset($item['variant_id']) || ! $item['variant_id'])) {
            throw new \InvalidArgumentException(
                "Product {$product->name} has variants but no variant was selected. Please choose a specific variant."
            );
        }

        // If this is truly a simple product (no variants), check its stock
        if (! $product->hasVariants()) {
            if ($product->track_inventory && $product->inventory_quantity < $item['quantity']) {
                throw new \InvalidArgumentException(
                    "Insufficient stock for {$product->name}. Available: {$product->inventory_quantity}, Requested: {$item['quantity']}"
                );
            }

            Log::info('Simple product stock validated', [
                'product_id' => $product->id,
                'available' => $product->inventory_quantity,
                'requested' => $item['quantity'],
            ]);
        }
    }

    /**
     * Validate stock for a product variant
     */
    protected function validateVariantStock(Product $product, array $item): void
    {
        $variant = $product->variants()->find($item['variant_id']);

        if (! $variant) {
            throw new \InvalidArgumentException("Product variant {$item['variant_id']} no longer exists for product {$product->name}");
        }

        if (! $variant->is_active) {
            throw new \InvalidArgumentException("Product variant is no longer available for {$product->name}");
        }

        // Check variant stock
        if ($product->track_inventory && $variant->inventory_quantity < $item['quantity']) {
            $variantName = $variant->getDisplayName();
            throw new \InvalidArgumentException(
                "Insufficient stock for {$variantName}. Available: {$variant->inventory_quantity}, Requested: {$item['quantity']}"
            );
        }

        Log::info('Variant stock validated', [
            'variant_id' => $variant->id,
            'available' => $variant->inventory_quantity,
            'requested' => $item['quantity'],
        ]);
    }

    /**
     * Find existing customer or create new one
     */
    protected function findOrCreateCustomer(array $customerData): Customer
    {
        // Try to find existing customer by email
        $customer = Customer::where('email', $customerData['email'])->first();

        if ($customer) {
            // Update customer data if provided
            $customer->update(array_filter([
                'first_name' => $customerData['first_name'] ?? $customer->first_name,
                'last_name' => $customerData['last_name'] ?? $customer->last_name,
                'phone' => $customerData['phone'] ?? $customer->phone,
            ]));
        } else {
            // Create new customer
            $customer = Customer::create([
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
            ]);
        }

        return $customer;
    }

    /**
     * Create or update customer address
     */
    protected function createOrUpdateAddress(Customer $customer, array $addressData, string $type): CustomerAddress
    {
        // Check if this exact address already exists
        $existingAddress = $customer->addresses()
            ->where('type', $type)
            ->where('address_line_1', $addressData['address_line_1'])
            ->where('postal_code', $addressData['postal_code'])
            ->first();

        if ($existingAddress) {
            return $existingAddress;
        }

        // Create new address
        return CustomerAddress::create(array_merge($addressData, [
            'customer_id' => $customer->id,
            'type' => $type,
            'is_default' => ! $customer->addresses()->where('type', $type)->exists(), // First address of this type is default
        ]));
    }

    /**
     * Get checkout summary
     */
    public function getCheckoutSummary(): array
    {
        $cart = $this->cartService->getCart();
        $total = $this->cartService->getTotal();

        $taxRate = 0.20; // 20% VAT
        $taxAmount = $total->value * $taxRate / (1 + $taxRate); // Tax included in price
        $subtotal = $total->value - $taxAmount;

        return [
            'items' => $cart,
            'item_count' => $cart->sum('quantity'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'shipping_amount' => 0, // TODO: Calculate shipping
            'total' => $total->value,
            'currency' => 'GBP',
        ];
    }
}
