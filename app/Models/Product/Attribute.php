<?php

// app/Models/Product/Attribute.php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Attribute extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'type', 'display_type', 'options', 'is_required',
        'is_variant', 'is_filterable', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_variant' => 'boolean',
        'is_filterable' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
            ->withPivot('is_required', 'is_variant', 'sort_order');
    }

    public function isColorSwatch(): bool
    {
        return $this->display_type === 'color_swatch';
    }

    public function isButton(): bool
    {
        return $this->display_type === 'button';
    }
}
