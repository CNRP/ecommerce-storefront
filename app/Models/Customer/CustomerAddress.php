<?php

// app/Models/Customer/CustomerAddress.php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state_county',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->company,
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state_county,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state_county' => $this->state_county,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ];
    }

    public static function createFromArray(array $addressData, Customer $customer, string $type): self
    {
        return self::create(array_merge($addressData, [
            'customer_id' => $customer->id,
            'type' => $type,
        ]));
    }
}
