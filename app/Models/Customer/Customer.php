<?php

// app/Models/Customer/Customer.php

namespace App\Models\Customer;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'email_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getDefaultBillingAddress(): ?CustomerAddress
    {
        return $this->addresses()
            ->where('type', 'billing')
            ->where('is_default', true)
            ->first();
    }

    public function getDefaultShippingAddress(): ?CustomerAddress
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();
    }

    public static function createFromCartData(array $customerData): self
    {
        return self::create([
            'first_name' => $customerData['first_name'],
            'last_name' => $customerData['last_name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'] ?? null,
        ]);
    }

    public static function findOrCreateByEmail(string $email, array $customerData = []): self
    {
        $customer = self::where('email', $email)->first();

        if (! $customer) {
            $customer = self::create(array_merge([
                'email' => $email,
            ], $customerData));
        }

        return $customer;
    }
}
