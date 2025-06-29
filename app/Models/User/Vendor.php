<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Vendor extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id', 'business_name', 'slug', 'description',
        'business_email', 'business_phone', 'business_address',
        'tax_id', 'status', 'commission_rate',
    ];

    protected $casts = [
        'business_address' => 'array',
        'commission_rate' => 'decimal:2',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('business_name')
            ->saveSlugsTo('slug');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(\App\Models\Product\Product::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
