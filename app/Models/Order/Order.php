<?php

// app/Models/Order/Order.php

namespace App\Models\Order;

use App\Models\Customer\Customer;
use App\Models\Payment\Payment;
use App\Models\User\Vendor;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_id',
        'status',
        'subtotal_amount',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'tax_rate',
        'tax_inclusive',
        'stripe_payment_intent_id',
        'payment_status',
        'payment_confirmed_at',
        'customer_details',
        'billing_address',
        'shipping_address',
        'metadata',
        'notes',
        'customer_notes',
        'estimated_delivery_date',
        'shipped_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
        'guest_token',
    ];

    protected $casts = [
        'subtotal_amount' => 'integer',
        'tax_amount' => 'integer',
        'shipping_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'tax_rate' => 'decimal:4',
        'tax_inclusive' => 'boolean',
        'customer_details' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'metadata' => 'array',
        'payment_confirmed_at' => 'datetime',
        'estimated_delivery_date' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopeRequiresProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeForCustomer($query, Customer $customer)
    {
        return $query->where('customer_id', $customer->id);
    }

    public function scopeByGuestToken($query, string $token)
    {
        return $query->where('guest_token', $token);
    }

    // Money value objects
    public function getSubtotalMoney(): Money
    {
        return Money::fromCents($this->subtotal_amount, $this->currency);
    }

    public function getTaxMoney(): Money
    {
        return Money::fromCents($this->tax_amount, $this->currency);
    }

    public function getShippingMoney(): Money
    {
        return Money::fromCents($this->shipping_amount, $this->currency);
    }

    public function getDiscountMoney(): Money
    {
        return Money::fromCents($this->discount_amount, $this->currency);
    }

    public function getTotalMoney(): Money
    {
        return Money::fromCents($this->total_amount, $this->currency);
    }

    // Status management
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            'pending_payment' => ['payment_failed', 'processing', 'cancelled'],
            'payment_failed' => ['pending_payment', 'cancelled'],
            'processing' => ['partially_fulfilled', 'fulfilled', 'cancelled'],
            'partially_fulfilled' => ['fulfilled', 'cancelled'],
            'fulfilled' => ['completed'],
            'completed' => ['refunded'],
            'cancelled' => [],
            'refunded' => [],
        ];

        return in_array($newStatus, $validTransitions[$this->status] ?? []);
    }

    public function updateStatus(string $newStatus, ?string $notes = null, ?\App\Models\User $user = null): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;

        // Set status-specific timestamps
        $now = now();
        match ($newStatus) {
            'payment_failed', 'cancelled' => $this->cancelled_at = $now,
            'fulfilled' => $this->shipped_at = $now,
            'delivered' => $this->delivered_at = $now,
            'completed' => $this->completed_at = $now,
            default => null,
        };

        $this->save();

        // Record status history
        $this->statusHistories()->create([
            'user_id' => $user?->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'notes' => $notes,
            'created_at' => $now,
        ]);

        return true;
    }

    // Payment management
    public function isPaid(): bool
    {
        return $this->payment_status === 'succeeded' && $this->payment_confirmed_at;
    }

    public function requiresPayment(): bool
    {
        return in_array($this->payment_status, ['pending', 'requires_action', 'requires_payment_method']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending_payment', 'payment_failed', 'processing']);
    }

    public function canBeRefunded(): bool
    {
        return $this->isPaid() && in_array($this->status, ['processing', 'fulfilled', 'completed']);
    }

    // Item management
    public function getTotalItems(): int
    {
        return $this->items()->sum('quantity');
    }

    public function getTotalFulfilledItems(): int
    {
        return $this->items()->sum('quantity_fulfilled');
    }

    public function isFullyFulfilled(): bool
    {
        return $this->getTotalItems() === $this->getTotalFulfilledItems();
    }

    public function isPartiallyFulfilled(): bool
    {
        $fulfilled = $this->getTotalFulfilledItems();

        return $fulfilled > 0 && $fulfilled < $this->getTotalItems();
    }

    // Static factory methods
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.date('Y').'-'.str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    public static function generateGuestToken(): string
    {
        return Str::random(32);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (! $order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }

            if (! $order->guest_token) {
                $order->guest_token = self::generateGuestToken();
            }
        });
    }
}
