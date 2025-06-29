<?php

// app/Models/Payment/Payment.php

namespace App\Models\Payment;

use App\Models\Order\Order;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'stripe_customer_id',
        'type',
        'status',
        'amount',
        'amount_received',
        'application_fee',
        'currency',
        'payment_method_type',
        'payment_method_details',
        'stripe_data',
        'processed_at',
        'failure_reason',
        'failure_message',
    ];

    protected $casts = [
        'amount' => 'integer',
        'amount_received' => 'integer',
        'application_fee' => 'integer',
        'payment_method_details' => 'array',
        'stripe_data' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Money value objects
    public function getAmountMoney(): Money
    {
        return Money::fromCents($this->amount, $this->currency);
    }

    public function getAmountReceivedMoney(): ?Money
    {
        return $this->amount_received ? Money::fromCents($this->amount_received, $this->currency) : null;
    }

    // Helper methods
    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    public function requiresAction(): bool
    {
        return in_array($this->status, ['requires_action', 'requires_confirmation', 'requires_payment_method']);
    }

    public function getPaymentMethodDescription(): string
    {
        if (! $this->payment_method_details) {
            return 'Unknown payment method';
        }

        $details = $this->payment_method_details;

        if ($this->payment_method_type === 'card') {
            $brand = ucfirst($details['brand'] ?? 'card');
            $last4 = $details['last4'] ?? '****';

            return "{$brand} ending in {$last4}";
        }

        return ucfirst($this->payment_method_type ?? 'payment method');
    }

    public static function createFromStripePaymentIntent(array $paymentIntent, Order $order): self
    {
        $paymentMethod = $paymentIntent['charges']['data'][0]['payment_method_details'] ?? null;

        return self::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => $paymentIntent['id'],
            'stripe_payment_method_id' => $paymentIntent['payment_method'] ?? null,
            'stripe_customer_id' => $paymentIntent['customer'] ?? null,
            'type' => 'payment',
            'status' => $paymentIntent['status'],
            'amount' => $paymentIntent['amount'],
            'amount_received' => $paymentIntent['amount_received'] ?? null,
            'currency' => strtoupper($paymentIntent['currency']),
            'payment_method_type' => $paymentMethod['type'] ?? null,
            'payment_method_details' => $paymentMethod ? [
                'brand' => $paymentMethod['card']['brand'] ?? null,
                'last4' => $paymentMethod['card']['last4'] ?? null,
                'exp_month' => $paymentMethod['card']['exp_month'] ?? null,
                'exp_year' => $paymentMethod['card']['exp_year'] ?? null,
            ] : null,
            'stripe_data' => $paymentIntent,
            'processed_at' => $paymentIntent['status'] === 'succeeded' ? now() : null,
        ]);
    }
}
