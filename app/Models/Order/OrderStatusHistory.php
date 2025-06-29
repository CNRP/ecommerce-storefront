<?php

// app/Models/Order/OrderStatusHistory.php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'from_status',
        'to_status',
        'notes',
        'metadata',
        'customer_notified',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'customer_notified' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getStatusChangeDescription(): string
    {
        $from = $this->from_status ? str_replace('_', ' ', ucfirst($this->from_status)) : 'Created';
        $to = str_replace('_', ' ', ucfirst($this->to_status));

        return "Status changed from {$from} to {$to}";
    }

    public function getUserName(): string
    {
        return $this->user?->name ?? 'System';
    }
}
