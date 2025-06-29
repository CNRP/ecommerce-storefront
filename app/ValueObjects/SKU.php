<?php

namespace App\ValueObjects;

use App\Models\Product\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SKU
{
    public function __construct(
        public readonly string $value
    ) {}

    public static function generate(Product $product, ?Collection $attributeValues = null): string
    {
        $baseSku = $product->sku ?? self::generateBaseSku($product->name);

        if (! $attributeValues || $attributeValues->isEmpty()) {
            return $baseSku;
        }

        // Create variant suffix from attribute values
        $suffix = $attributeValues
            ->map(fn ($av) => self::sanitizeForSku($av->value))
            ->implode('-');

        return $baseSku.'-'.$suffix;
    }

    public static function generateBaseSku(string $productName, ?string $vendorPrefix = null): string
    {
        $cleaned = self::sanitizeForSku($productName);
        $prefix = $vendorPrefix ? strtoupper($vendorPrefix).'-' : '';

        return $prefix.strtoupper($cleaned);
    }

    public static function sanitizeForSku(string $input): string
    {
        // Remove special characters, convert to uppercase, replace spaces with hyphens
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(str_replace(' ', '', $input)));
    }

    public static function ensureUnique(string $baseSku, string $table = 'products', string $column = 'sku'): string
    {
        $originalSku = $baseSku;
        $counter = 1;

        while (DB::table($table)->where($column, $baseSku)->exists()) {
            $baseSku = $originalSku.'-'.$counter;
            $counter++;
        }

        return $baseSku;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(SKU $other): bool
    {
        return $this->value === $other->value;
    }
}
