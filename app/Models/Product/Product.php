<?php

// app/Models/Product/Product.php - Updated with smart stock management

namespace App\Models\Product;

use App\Models\User\Vendor;
use App\Traits\HasSEO;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasSEO, HasSlug, Searchable;

    protected $fillable = [
        'vendor_id', 'name', 'slug', 'description', 'short_description',
        'sku', 'type', 'status', 'price', 'compare_price', 'cost_price',
        'track_inventory', 'inventory_quantity', 'low_stock_threshold',
        'weight', 'dimensions', 'seo_meta', 'tags', 'image', 'gallery',
        'is_featured', 'sort_order', 'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'seo_meta' => 'array',
        'tags' => 'array',
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'track_inventory' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('is_required', 'is_variant');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            // Products that don't track inventory are always "in stock"
            $q->where('track_inventory', false)
              // OR simple products with inventory
                ->orWhere(function ($subQ) {
                    $subQ->where('track_inventory', true)
                        ->where('type', 'simple')
                        ->where('inventory_quantity', '>', 0);
                })
              // OR variable products that have variants with inventory
                ->orWhere(function ($subQ) {
                    $subQ->where('track_inventory', true)
                        ->where('type', 'variable')
                        ->whereHas('variants', function ($variantQ) {
                            $variantQ->where('inventory_quantity', '>', 0);
                        });
                });
        });
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeLowStock($query)
    {
        return $query->where(function ($q) {
            // Simple products with low stock
            $q->where(function ($subQ) {
                $subQ->where('track_inventory', true)
                    ->whereDoesntHave('variants')
                    ->whereRaw('inventory_quantity <= low_stock_threshold');
            })
            // OR variable products where total variant stock is low
                ->orWhere(function ($subQ) {
                    $subQ->where('track_inventory', true)
                        ->whereHas('variants')
                        ->whereRaw('(SELECT SUM(inventory_quantity) FROM product_variants WHERE product_id = products.id) <= low_stock_threshold');
                });
        });
    }

    // Helper methods
    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function getMainVariant(): ?ProductVariant
    {
        return $this->variants()->orderBy('position')->first();
    }

    public function isInStock(): bool
    {
        if (! $this->track_inventory) {
            return true;
        }

        if ($this->hasVariants()) {
            return $this->variants()->where('inventory_quantity', '>', 0)->exists();
        }

        return $this->inventory_quantity > 0;
    }

    /**
     * Get the effective stock quantity for display purposes
     * - For simple products: returns inventory_quantity
     * - For products with variants: returns sum of all variant quantities
     * - For products not tracking inventory: returns null
     */
    public function getEffectiveStockQuantity(): ?int
    {
        if (! $this->track_inventory) {
            return null;
        }

        if ($this->hasVariants()) {
            return $this->variants()->sum('inventory_quantity');
        }

        return $this->inventory_quantity;
    }

    /**
     * Get stock status for display purposes
     */
    public function getStockStatus(): string
    {
        if (! $this->track_inventory) {
            return 'in_stock';
        }

        $effectiveStock = $this->getEffectiveStockQuantity();

        if ($effectiveStock <= 0) {
            return 'out_of_stock';
        }

        if ($effectiveStock <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Check if product is considered low stock
     */
    public function isLowStock(): bool
    {
        if (! $this->track_inventory) {
            return false;
        }

        $effectiveStock = $this->getEffectiveStockQuantity();

        return $effectiveStock > 0 && $effectiveStock <= $this->low_stock_threshold;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock(): bool
    {
        if (! $this->track_inventory) {
            return false;
        }

        return $this->getEffectiveStockQuantity() <= 0;
    }

    /**
     * Get stock color for badges/display
     */
    public function getStockColor(): string
    {
        $status = $this->getStockStatus();

        return match ($status) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'in_stock' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get human-readable stock text
     */
    public function getStockText(): string
    {
        if (! $this->track_inventory) {
            return 'In Stock';
        }

        $effectiveStock = $this->getEffectiveStockQuantity();

        if ($effectiveStock <= 0) {
            return 'Out of Stock';
        }

        if ($this->hasVariants()) {
            $inStockVariants = $this->variants()->where('inventory_quantity', '>', 0)->count();
            $totalVariants = $this->variants()->count();

            return "{$effectiveStock} units ({$inStockVariants}/{$totalVariants} variants in stock)";
        }

        return "{$effectiveStock} units";
    }

    public function getDiscountPercentage(): ?float
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getMainImage(): ?string
    {
        return $this->image ?? ($this->gallery && count($this->gallery) > 0 ? $this->gallery[0] : null);
    }

    public function getVendorName(): string
    {
        return $this->vendor?->business_name ?? 'Main Store';
    }
}
