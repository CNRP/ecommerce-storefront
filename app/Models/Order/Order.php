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
    // Define valid order statuses as constants for better maintainability
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAYMENT_FAILED = 'payment_failed';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PARTIALLY_FULFILLED = 'partially_fulfilled';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    // Define valid payment statuses
    public const PAYMENT_STATUS_PENDING = 'pending';

    public const PAYMENT_STATUS_PROCESSING = 'processing';

    public const PAYMENT_STATUS_REQUIRES_ACTION = 'requires_action';

    public const PAYMENT_STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    public const PAYMENT_STATUS_SUCCEEDED = 'succeeded';

    public const PAYMENT_STATUS_FAILED = 'failed';

    public const PAYMENT_STATUS_CANCELLED = 'cancelled';

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
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePendingPayment($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }

    public function scopeRequiresProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
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
            self::STATUS_DRAFT => [self::STATUS_PENDING_PAYMENT, self::STATUS_CANCELLED],
            self::STATUS_PENDING_PAYMENT => [self::STATUS_PAYMENT_FAILED, self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PAYMENT_FAILED => [self::STATUS_PENDING_PAYMENT, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_PARTIALLY_FULFILLED, self::STATUS_FULFILLED, self::STATUS_CANCELLED],
            self::STATUS_PARTIALLY_FULFILLED => [self::STATUS_FULFILLED, self::STATUS_CANCELLED],
            self::STATUS_FULFILLED => [self::STATUS_COMPLETED],
            self::STATUS_COMPLETED => [self::STATUS_REFUNDED],
            self::STATUS_CANCELLED => [],
            self::STATUS_REFUNDED => [],
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
            self::STATUS_PAYMENT_FAILED, self::STATUS_CANCELLED => $this->cancelled_at = $now,
            self::STATUS_FULFILLED => $this->shipped_at = $now,
            'delivered' => $this->delivered_at = $now,
            self::STATUS_COMPLETED => $this->completed_at = $now,
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
        return $this->payment_status === self::PAYMENT_STATUS_SUCCEEDED && $this->payment_confirmed_at;
    }

    public function requiresPayment(): bool
    {
        return in_array($this->payment_status, [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_REQUIRES_ACTION,
            self::PAYMENT_STATUS_REQUIRES_PAYMENT_METHOD,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PAYMENT_FAILED,
            self::STATUS_PROCESSING,
        ]);
    }

    public function canBeRefunded(): bool
    {
        return $this->isPaid() && in_array($this->status, [
            self::STATUS_PROCESSING,
            self::STATUS_FULFILLED,
            self::STATUS_COMPLETED,
        ]);
    }

    // Status checking methods
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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

    // Static methods for getting valid statuses
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PAYMENT_FAILED,
            self::STATUS_PROCESSING,
            self::STATUS_PARTIALLY_FULFILLED,
            self::STATUS_FULFILLED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
        ];
    }

    public static function getValidPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_PROCESSING,
            self::PAYMENT_STATUS_REQUIRES_ACTION,
            self::PAYMENT_STATUS_REQUIRES_PAYMENT_METHOD,
            self::PAYMENT_STATUS_SUCCEEDED,
            self::PAYMENT_STATUS_FAILED,
            self::PAYMENT_STATUS_CANCELLED,
        ];
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

            // Set default status if not provided
            if (! $order->status) {
                $order->status = self::STATUS_DRAFT;
            }
        });
    }
}
