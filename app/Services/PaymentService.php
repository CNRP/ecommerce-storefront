<?php

// app/Services/PaymentService.php - Updated to work with CheckoutService

namespace App\Services;

use App\Models\Customer\Customer;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Payment Intent for an order
     */
    public function createPaymentIntent(Order $order, Customer $customer): array
    {
        try {
            // Ensure customer has a Stripe customer ID
            $stripeCustomerId = $this->getOrCreateStripeCustomer($customer);

            $paymentIntentData = [
                'amount' => $order->total_amount, // Amount in pence
                'currency' => strtolower($order->currency),
                'customer' => $stripeCustomerId,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                ],
                'description' => "Order {$order->order_number}",
                'receipt_email' => $customer->email,
                'shipping' => [
                    'name' => $order->shipping_address['first_name'].' '.$order->shipping_address['last_name'],
                    'address' => [
                        'line1' => $order->shipping_address['address_line_1'],
                        'line2' => $order->shipping_address['address_line_2'] ?? null,
                        'city' => $order->shipping_address['city'],
                        'state' => $order->shipping_address['state_county'],
                        'postal_code' => $order->shipping_address['postal_code'],
                        'country' => $order->shipping_address['country'],
                    ],
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ];

            $paymentIntent = $this->stripe->paymentIntents->create($paymentIntentData);

            // Update order with Stripe Payment Intent ID and move to pending_payment
            $order->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
                'payment_status' => $paymentIntent->status,
            ]);

            // Update order status from draft to pending_payment
            if ($order->status === 'draft') {
                app(OrderService::class)->updateOrderStatus($order, 'pending_payment', 'Payment intent created');
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_customer_id' => $stripeCustomerId,
                'type' => 'payment',
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => strtoupper($paymentIntent->currency),
                'stripe_data' => $paymentIntent->toArray(),
            ]);

            return $paymentIntent->toArray();

        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to create payment intent: '.$e->getMessage());
        }
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentSucceeded(array $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if (! $order) {
            Log::warning('Order not found for successful payment', ['payment_intent_id' => $paymentIntent['id']]);

            return;
        }

        // Update order payment status
        $order->update([
            'payment_status' => 'succeeded',
            'payment_confirmed_at' => now(),
        ]);

        // Update payment record
        $payment = $order->payments()->where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        if ($payment) {
            $payment->update([
                'status' => 'succeeded',
                'amount_received' => $paymentIntent['amount_received'],
                'payment_method_type' => $paymentIntent['charges']['data'][0]['payment_method_details']['type'] ?? null,
                'payment_method_details' => $this->extractPaymentMethodDetails($paymentIntent),
                'stripe_data' => $paymentIntent,
                'processed_at' => now(),
            ]);
        }

        // Transition order to processing - use CheckoutService for this
        app(CheckoutService::class)->confirmPayment($order);

        Log::info('Payment succeeded', ['order_id' => $order->id, 'order_number' => $order->order_number]);
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed(array $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if (! $order) {
            Log::warning('Order not found for failed payment', ['payment_intent_id' => $paymentIntent['id']]);

            return;
        }

        // Update order payment status
        $order->update(['payment_status' => 'failed']);

        // Update payment record
        $payment = $order->payments()->where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $paymentIntent['last_payment_error']['code'] ?? 'unknown',
                'failure_message' => $paymentIntent['last_payment_error']['message'] ?? 'Payment failed',
                'stripe_data' => $paymentIntent,
            ]);
        }

        // Update order status
        app(OrderService::class)->updateOrderStatus($order, 'payment_failed', 'Payment failed');

        Log::info('Payment failed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'failure_reason' => $paymentIntent['last_payment_error']['code'] ?? 'unknown',
        ]);
    }

    /**
     * Confirm a payment intent (if needed)
     */
    public function confirmPaymentIntent(string $paymentIntentId, ?string $paymentMethodId = null): array
    {
        try {
            $confirmData = [];

            if ($paymentMethodId) {
                $confirmData['payment_method'] = $paymentMethodId;
            }

            $paymentIntent = $this->stripe->paymentIntents->confirm($paymentIntentId, $confirmData);

            return $paymentIntent->toArray();

        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent confirmation failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to confirm payment: '.$e->getMessage());
        }
    }

    /**
     * Retrieve a payment intent from Stripe
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve Payment Intent', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to retrieve payment: '.$e->getMessage());
        }
    }

    /**
     * Cancel a payment intent
     */
    public function cancelPaymentIntent(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->cancel($paymentIntentId);

            // Update local payment record
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($payment) {
                $payment->update([
                    'status' => $paymentIntent->status,
                    'stripe_data' => $paymentIntent->toArray(),
                ]);
            }

            return $paymentIntent->toArray();

        } catch (ApiErrorException $e) {
            Log::error('Failed to cancel Payment Intent', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to cancel payment: '.$e->getMessage());
        }
    }

    /**
     * Create a refund for a payment
     */
    public function refundPayment(string $paymentIntentId, ?int $amount = null, string $reason = 'requested_by_customer'): array
    {
        try {
            $refundData = [
                'payment_intent' => $paymentIntentId,
                'reason' => $reason,
            ];

            if ($amount) {
                $refundData['amount'] = $amount;
            }

            $refund = $this->stripe->refunds->create($refundData);

            // Create refund payment record
            $order = Order::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($order) {
                Payment::create([
                    'order_id' => $order->id,
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'type' => 'refund',
                    'status' => 'succeeded',
                    'amount' => -$refund->amount, // Negative for refund
                    'currency' => strtoupper($refund->currency),
                    'stripe_data' => $refund->toArray(),
                    'processed_at' => now(),
                ]);
            }

            return $refund->toArray();

        } catch (ApiErrorException $e) {
            Log::error('Failed to create refund', [
                'payment_intent_id' => $paymentIntentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to process refund: '.$e->getMessage());
        }
    }

    /**
     * Get or create a Stripe customer
     */
    public function getOrCreateStripeCustomer(Customer $customer): string
    {
        if ($customer->stripe_customer_id) {
            // Verify the customer still exists in Stripe
            try {
                $this->stripe->customers->retrieve($customer->stripe_customer_id);

                return $customer->stripe_customer_id;
            } catch (ApiErrorException $e) {
                // Customer doesn't exist, create a new one
                Log::warning('Stripe customer not found, creating new one', [
                    'customer_id' => $customer->id,
                    'stripe_customer_id' => $customer->stripe_customer_id,
                ]);
            }
        }

        // Create new Stripe customer
        try {
            $stripeCustomer = $this->stripe->customers->create([
                'email' => $customer->email,
                'name' => $customer->full_name,
                'phone' => $customer->phone,
                'metadata' => [
                    'customer_id' => $customer->id,
                ],
            ]);

            // Update customer with Stripe ID
            $customer->update(['stripe_customer_id' => $stripeCustomer->id]);

            return $stripeCustomer->id;

        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to create payment customer: '.$e->getMessage());
        }
    }

    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook(array $event): void
    {
        Log::info('Processing Stripe webhook', ['event_type' => $event['type']]);

        try {
            match ($event['type']) {
                'payment_intent.succeeded' => $this->handlePaymentSucceeded($event['data']['object']),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event['data']['object']),
                'payment_intent.requires_action' => $this->handlePaymentRequiresAction($event['data']['object']),
                'payment_intent.canceled' => $this->handlePaymentCanceled($event['data']['object']),
                'charge.dispute.created' => $this->handleChargeDispute($event['data']['object']),
                default => Log::info('Unhandled webhook event type: '.$event['type']),
            };
        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'event_type' => $event['type'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle payment requiring action
     */
    protected function handlePaymentRequiresAction(array $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if (! $order) {
            return;
        }

        $order->update(['payment_status' => 'requires_action']);

        $payment = $order->payments()->where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        if ($payment) {
            $payment->update([
                'status' => 'requires_action',
                'stripe_data' => $paymentIntent,
            ]);
        }

        Log::info('Payment requires action', ['order_id' => $order->id]);
    }

    /**
     * Handle canceled payment
     */
    protected function handlePaymentCanceled(array $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if (! $order) {
            return;
        }

        $order->update(['payment_status' => 'cancelled']);

        $payment = $order->payments()->where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        if ($payment) {
            $payment->update([
                'status' => 'cancelled',
                'stripe_data' => $paymentIntent,
            ]);
        }

        // Cancel the order
        app(OrderService::class)->cancelOrder($order, 'Payment canceled');

        Log::info('Payment canceled', ['order_id' => $order->id]);
    }

    /**
     * Handle charge dispute
     */
    protected function handleChargeDispute(array $dispute): void
    {
        $chargeId = $dispute['charge'];

        Log::warning('Charge dispute created', [
            'dispute_id' => $dispute['id'],
            'charge_id' => $chargeId,
            'amount' => $dispute['amount'],
            'reason' => $dispute['reason'],
        ]);
    }

    /**
     * Extract payment method details for storage
     */
    protected function extractPaymentMethodDetails(array $paymentIntent): ?array
    {
        $charges = $paymentIntent['charges']['data'] ?? [];
        if (empty($charges)) {
            return null;
        }

        $paymentMethodDetails = $charges[0]['payment_method_details'] ?? [];

        if (isset($paymentMethodDetails['card'])) {
            return [
                'type' => 'card',
                'brand' => $paymentMethodDetails['card']['brand'],
                'last4' => $paymentMethodDetails['card']['last4'],
                'exp_month' => $paymentMethodDetails['card']['exp_month'],
                'exp_year' => $paymentMethodDetails['card']['exp_year'],
                'country' => $paymentMethodDetails['card']['country'] ?? null,
            ];
        }

        return ['type' => $paymentMethodDetails['type'] ?? 'unknown'];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $webhookSecret) {
            Log::warning('Stripe webhook secret not configured');

            return false;
        }

        try {
            \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);

            return true;
        } catch (\Exception $e) {
            Log::error('Webhook signature verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get saved payment methods for a customer
     */
    public function getCustomerPaymentMethods(Customer $customer): array
    {
        if (! $customer->stripe_customer_id) {
            return [];
        }

        try {
            $paymentMethods = $this->stripe->paymentMethods->all([
                'customer' => $customer->stripe_customer_id,
                'type' => 'card',
            ]);

            return $paymentMethods->data;
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve customer payment methods', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Save a payment method for future use
     */
    public function savePaymentMethod(Customer $customer, string $paymentMethodId): bool
    {
        $stripeCustomerId = $this->getOrCreateStripeCustomer($customer);

        try {
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $stripeCustomerId,
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Failed to save payment method', [
                'customer_id' => $customer->id,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
