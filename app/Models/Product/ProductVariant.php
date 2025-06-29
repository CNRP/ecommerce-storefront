<?php

// app/Models/Product/ProductVariant.php

namespace App\Models\Product;

use App\ValueObjects\SKU;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'price', 'compare_price',
        'cost_price', 'inventory_quantity', 'weight', 'dimensions',
        'image', 'position', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values');
    }

    public function generateSKU(): string
    {
        return SKU::generate($this->product, $this->attributeValues);
    }

    public function ensureUniqueSKU(): string
    {
        $baseSku = $this->generateSKU();

        return SKU::ensureUnique($baseSku, 'product_variants', 'sku');
    }

    public function isInStock(): bool
    {
        return $this->inventory_quantity > 0;
    }

    public function getDisplayName(): string
    {
        $attributeNames = $this->attributeValues()
            ->with('attribute')
            ->get()
            ->map(fn ($av) => $av->attribute->name.': '.$av->display_label)
            ->implode(', ');

        return $this->product->name.($attributeNames ? " ({$attributeNames})" : '');
    }

    public function getDiscountPercentage(): ?float
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }
}
