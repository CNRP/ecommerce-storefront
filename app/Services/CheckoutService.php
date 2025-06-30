<?php

// app/Services/CheckoutService.php - Updated with proper guest/login flow

namespace App\Services;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerAddress;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Initialize checkout process with proper guest/login handling
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

        return DB::transaction(function () use ($checkoutData, $cart) {
            // Handle customer creation/retrieval with proper guest/login flow
            $customerResult = $this->handleCustomerFlow($checkoutData);

            // Create or update addresses - with proper validation
            $billingAddress = $this->createOrUpdateAddress(
                $customerResult['customer'],
                $checkoutData['billing_address'],
                'billing'
            );

            // Handle shipping address based on sameAsBilling flag
            $shippingAddressData = $checkoutData['same_as_billing'] ?? false
                ? $checkoutData['billing_address']
                : $checkoutData['shipping_address'];

            $shippingAddress = $this->createOrUpdateAddress(
                $customerResult['customer'],
                $shippingAddressData,
                'shipping'
            );

            // Create the order with "draft" status initially
            $order = $this->orderService->createOrder(
                $customerResult['customer'],
                $cart,
                $billingAddress->toArray(),
                $shippingAddress->toArray(),
                array_merge($checkoutData['order_data'] ?? [], [
                    'status' => 'draft', // Start as draft, not pending_payment
                ])
            );

            // Create Stripe Payment Intent
            $paymentIntent = $this->paymentService->createPaymentIntent($order, $customerResult['customer']);

            return [
                'order' => $order,
                'customer' => $customerResult['customer'],
                'customer_created' => $customerResult['created'] ?? false,
                'user_created' => $customerResult['user_created'] ?? false,
                'payment_intent' => $paymentIntent,
                'client_secret' => $paymentIntent['client_secret'],
            ];
        });
    }

    /**
     * Handle customer flow - guest, existing, or new user creation
     */
    protected function handleCustomerFlow(array $checkoutData): array
    {
        $customerData = $checkoutData['customer'];
        $email = $customerData['email'];
        $result = ['created' => false, 'user_created' => false];

        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            $customer = Customer::where('user_id', $user->id)->first();

            if (! $customer) {
                // Create customer record for existing user
                $customer = Customer::create([
                    'user_id' => $user->id,
                    'first_name' => $customerData['first_name'],
                    'last_name' => $customerData['last_name'],
                    'email' => $user->email,
                    'phone' => $customerData['phone'] ?? null,
                ]);
                $result['created'] = true;
            } else {
                // Update existing customer data
                $customer->update(array_filter([
                    'first_name' => $customerData['first_name'] ?? $customer->first_name,
                    'last_name' => $customerData['last_name'] ?? $customer->last_name,
                    'phone' => $customerData['phone'] ?? $customer->phone,
                ]));
            }

            $result['customer'] = $customer;

            return $result;
        }

        // Handle guest checkout
        $existingCustomer = Customer::where('email', $email)->first();

        if ($existingCustomer) {
            // Update existing customer
            $existingCustomer->update(array_filter([
                'first_name' => $customerData['first_name'] ?? $existingCustomer->first_name,
                'last_name' => $customerData['last_name'] ?? $existingCustomer->last_name,
                'phone' => $customerData['phone'] ?? $existingCustomer->phone,
            ]));

            $result['customer'] = $existingCustomer;

            return $result;
        }

        // Check if we should create a user account
        $createAccount = $checkoutData['create_account'] ?? false;
        $password = $checkoutData['password'] ?? null;

        if ($createAccount && $password) {
            // Create user account
            $user = User::create([
                'name' => $customerData['first_name'].' '.$customerData['last_name'],
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify since they're purchasing
            ]);

            // Create customer linked to user
            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'email' => $email,
                'phone' => $customerData['phone'] ?? null,
            ]);

            // Log them in
            Auth::login($user);

            $result['customer'] = $customer;
            $result['created'] = true;
            $result['user_created'] = true;

            return $result;
        }

        // Create guest customer (no user account)
        $customer = Customer::create([
            'first_name' => $customerData['first_name'],
            'last_name' => $customerData['last_name'],
            'email' => $email,
            'phone' => $customerData['phone'] ?? null,
        ]);

        $result['customer'] = $customer;
        $result['created'] = true;

        return $result;
    }

    /**
     * Complete checkout after successful payment
     */
    public function completeCheckout(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            // Update order status to pending_payment (now that payment is being processed)
            $this->orderService->updateOrderStatus($order, 'pending_payment', 'Payment intent created');

            // Don't clear cart yet - wait for payment confirmation

            return [
                'order' => $order->fresh(['items', 'customer']),
                'success' => true,
                'message' => 'Checkout initialized successfully',
            ];
        });
    }

    /**
     * Handle successful payment confirmation
     */
    public function confirmPayment(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            // Update order status to processing
            $this->orderService->updateOrderStatus($order, 'processing', 'Payment confirmed');

            // Clear the cart only after successful payment
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

        // Don't clear cart on failure - let user retry
    }

    /**
     * Create or update customer address with proper field validation
     */
    protected function createOrUpdateAddress(Customer $customer, array $addressData, string $type): CustomerAddress
    {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'address_line_1', 'city', 'state_county', 'postal_code', 'country'];

        foreach ($requiredFields as $field) {
            if (empty($addressData[$field])) {
                throw new \InvalidArgumentException("Missing required address field: {$field}");
            }
        }

        // Ensure state_county is properly preserved
        if (strlen($addressData['state_county']) < 2) {
            throw new \InvalidArgumentException('State/County field appears to be truncated');
        }

        // Check if this exact address already exists
        $existingAddress = $customer->addresses()
            ->where('type', $type)
            ->where('address_line_1', $addressData['address_line_1'])
            ->where('postal_code', $addressData['postal_code'])
            ->where('state_county', $addressData['state_county'])
            ->first();

        if ($existingAddress) {
            return $existingAddress;
        }

        // Create new address
        return CustomerAddress::create(array_merge($addressData, [
            'customer_id' => $customer->id,
            'type' => $type,
            'is_default' => ! $customer->addresses()->where('type', $type)->exists(),
        ]));
    }

    /**
     * Validate cart stock (existing method - keeping as is since it works)
     */
    protected function validateCartStock(\Illuminate\Support\Collection $cart): void
    {
        foreach ($cart as $key => $item) {
            Log::info('Validating cart item', ['key' => $key, 'item' => $item]);

            if (! isset($item['product_id']) || ! $item['product_id']) {
                throw new \InvalidArgumentException("Cart item missing product_id: {$key}");
            }

            $product = Product::with(['variants'])->find($item['product_id']);

            if (! $product) {
                throw new \InvalidArgumentException("Product {$item['product_id']} no longer exists");
            }

            if ($product->status !== 'published') {
                throw new \InvalidArgumentException("Product {$product->name} is no longer available");
            }

            if (! $product->track_inventory) {
                continue;
            }

            if (isset($item['variant_id']) && $item['variant_id'] && $product->hasVariants()) {
                $this->validateVariantStock($product, $item);
            } else {
                $this->validateSimpleProductStock($product, $item);
            }
        }
    }

    /**
     * Validate stock for a simple product
     */
    protected function validateSimpleProductStock(Product $product, array $item): void
    {
        if ($product->hasVariants() && (! isset($item['variant_id']) || ! $item['variant_id'])) {
            throw new \InvalidArgumentException(
                "Product {$product->name} has variants but no variant was selected."
            );
        }

        if (! $product->hasVariants()) {
            if ($product->track_inventory && $product->inventory_quantity < $item['quantity']) {
                throw new \InvalidArgumentException(
                    "Insufficient stock for {$product->name}. Available: {$product->inventory_quantity}, Requested: {$item['quantity']}"
                );
            }
        }
    }

    /**
     * Validate stock for a product variant
     */
    protected function validateVariantStock(Product $product, array $item): void
    {
        $variant = $product->variants()->find($item['variant_id']);

        if (! $variant) {
            throw new \InvalidArgumentException("Product variant {$item['variant_id']} no longer exists");
        }

        if (! $variant->is_active) {
            throw new \InvalidArgumentException('Product variant is no longer available');
        }

        if ($product->track_inventory && $variant->inventory_quantity < $item['quantity']) {
            throw new \InvalidArgumentException(
                "Insufficient stock for {$variant->getDisplayName()}. Available: {$variant->inventory_quantity}, Requested: {$item['quantity']}"
            );
        }
    }

    /**
     * Get checkout summary
     */
    public function getCheckoutSummary(): array
    {
        $cart = $this->cartService->getCart();
        $total = $this->cartService->getTotal();

        $taxRate = 0.20;
        $taxAmount = $total->value * $taxRate / (1 + $taxRate);
        $subtotal = $total->value - $taxAmount;

        return [
            'items' => $cart,
            'item_count' => $cart->sum('quantity'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'shipping_amount' => 0,
            'total' => $total->value,
            'currency' => 'GBP',
        ];
    }
}
