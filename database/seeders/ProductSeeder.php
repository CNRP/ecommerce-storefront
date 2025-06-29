<?php

namespace Database\Seeders;

use App\Models\Product\Attribute;
use App\Models\Product\AttributeValue;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Create attributes first
        $this->createAttributes();

        $categories = Category::all();

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with titanium design and A17 Pro chip. Features advanced camera system with 5x optical zoom.',
                'short_description' => 'Latest iPhone with titanium design',
                'sku' => 'IPHONE-15-PRO',
                'price' => 999.00,
                'compare_price' => 1099.00,
                'cost_price' => 650.00,
                'inventory_quantity' => 0, // Will be managed by variants
                'type' => 'variable',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
                'tags' => ['smartphone', 'apple', 'premium'],
                'category_names' => ['Smartphones', 'Electronics'],
                'variants' => [
                    ['color' => 'Natural Titanium', 'storage' => '128GB', 'price_modifier' => 0],
                    ['color' => 'Natural Titanium', 'storage' => '256GB', 'price_modifier' => 100],
                    ['color' => 'Blue Titanium', 'storage' => '128GB', 'price_modifier' => 0],
                    ['color' => 'Blue Titanium', 'storage' => '256GB', 'price_modifier' => 100],
                    ['color' => 'White Titanium', 'storage' => '128GB', 'price_modifier' => 0],
                    ['color' => 'Black Titanium', 'storage' => '256GB', 'price_modifier' => 100],
                ],
            ],
            [
                'name' => 'MacBook Air M3',
                'description' => 'The incredibly thin and light MacBook Air is more powerful than ever. Featuring the breakthrough M3 chip, up to 18 hours of battery life, and a brilliant Liquid Retina display. Available in multiple configurations to fit your workflow.',
                'short_description' => 'Supercharged by the M3 chip with incredible performance and battery life',
                'sku' => 'MACBOOK-AIR-M3',
                'price' => 1099.00, // Base price for 13" 256GB
                'compare_price' => 1199.00,
                'cost_price' => 800.00,
                'inventory_quantity' => 0, // Managed by variants
                'type' => 'variable',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
                'tags' => ['laptop', 'apple', 'computer', 'm3', 'macbook'],
                'category_names' => ['Laptops', 'Electronics'],
                'variants' => [
                    // 13-inch models
                    ['screen_size' => '13-inch', 'storage' => '256GB', 'color' => 'Midnight', 'price_modifier' => 0],
                    ['screen_size' => '13-inch', 'storage' => '256GB', 'color' => 'Silver', 'price_modifier' => 0],
                    ['screen_size' => '13-inch', 'storage' => '256GB', 'color' => 'Space Gray', 'price_modifier' => 0],
                    ['screen_size' => '13-inch', 'storage' => '256GB', 'color' => 'Starlight', 'price_modifier' => 0],

                    ['screen_size' => '13-inch', 'storage' => '512GB', 'color' => 'Midnight', 'price_modifier' => 200],
                    ['screen_size' => '13-inch', 'storage' => '512GB', 'color' => 'Silver', 'price_modifier' => 200],
                    ['screen_size' => '13-inch', 'storage' => '512GB', 'color' => 'Space Gray', 'price_modifier' => 200],
                    ['screen_size' => '13-inch', 'storage' => '512GB', 'color' => 'Starlight', 'price_modifier' => 200],

                    ['screen_size' => '13-inch', 'storage' => '1TB', 'color' => 'Midnight', 'price_modifier' => 400],
                    ['screen_size' => '13-inch', 'storage' => '1TB', 'color' => 'Silver', 'price_modifier' => 400],
                    ['screen_size' => '13-inch', 'storage' => '1TB', 'color' => 'Space Gray', 'price_modifier' => 400],
                    ['screen_size' => '13-inch', 'storage' => '1TB', 'color' => 'Starlight', 'price_modifier' => 400],

                    ['screen_size' => '13-inch', 'storage' => '2TB', 'color' => 'Midnight', 'price_modifier' => 800],
                    ['screen_size' => '13-inch', 'storage' => '2TB', 'color' => 'Silver', 'price_modifier' => 800],
                    ['screen_size' => '13-inch', 'storage' => '2TB', 'color' => 'Space Gray', 'price_modifier' => 800],

                    // 15-inch models (higher base price)
                    ['screen_size' => '15-inch', 'storage' => '256GB', 'color' => 'Midnight', 'price_modifier' => 200],
                    ['screen_size' => '15-inch', 'storage' => '256GB', 'color' => 'Silver', 'price_modifier' => 200],
                    ['screen_size' => '15-inch', 'storage' => '256GB', 'color' => 'Space Gray', 'price_modifier' => 200],
                    ['screen_size' => '15-inch', 'storage' => '256GB', 'color' => 'Starlight', 'price_modifier' => 200],

                    ['screen_size' => '15-inch', 'storage' => '512GB', 'color' => 'Midnight', 'price_modifier' => 400],
                    ['screen_size' => '15-inch', 'storage' => '512GB', 'color' => 'Silver', 'price_modifier' => 400],
                    ['screen_size' => '15-inch', 'storage' => '512GB', 'color' => 'Space Gray', 'price_modifier' => 400],
                    ['screen_size' => '15-inch', 'storage' => '512GB', 'color' => 'Starlight', 'price_modifier' => 400],

                    ['screen_size' => '15-inch', 'storage' => '1TB', 'color' => 'Midnight', 'price_modifier' => 600],
                    ['screen_size' => '15-inch', 'storage' => '1TB', 'color' => 'Silver', 'price_modifier' => 600],
                    ['screen_size' => '15-inch', 'storage' => '1TB', 'color' => 'Space Gray', 'price_modifier' => 600],
                    ['screen_size' => '15-inch', 'storage' => '1TB', 'color' => 'Starlight', 'price_modifier' => 600],

                    ['screen_size' => '15-inch', 'storage' => '2TB', 'color' => 'Midnight', 'price_modifier' => 1000],
                    ['screen_size' => '15-inch', 'storage' => '2TB', 'color' => 'Silver', 'price_modifier' => 1000],
                    ['screen_size' => '15-inch', 'storage' => '2TB', 'color' => 'Space Gray', 'price_modifier' => 1000],
                ],
            ],
            [
                'name' => 'Classic Cotton T-Shirt',
                'description' => 'Premium cotton t-shirt available in multiple colors and sizes. Perfect for everyday wear with comfortable fit.',
                'short_description' => 'Premium cotton t-shirt',
                'sku' => 'TSHIRT-COTTON-CLASSIC',
                'price' => 29.99,
                'compare_price' => 39.99,
                'cost_price' => 12.00,
                'inventory_quantity' => 0, // Will be managed by variants
                'type' => 'variable',
                'status' => 'published',
                'is_featured' => false,
                'published_at' => now(),
                'tags' => ['clothing', 'cotton', 'basic'],
                'category_names' => ['Men\'s Clothing', 'Clothing'],
                'variants' => [
                    ['color' => 'White', 'size' => 'S', 'price_modifier' => 0],
                    ['color' => 'White', 'size' => 'M', 'price_modifier' => 0],
                    ['color' => 'White', 'size' => 'L', 'price_modifier' => 0],
                    ['color' => 'White', 'size' => 'XL', 'price_modifier' => 2],
                    ['color' => 'Black', 'size' => 'S', 'price_modifier' => 0],
                    ['color' => 'Black', 'size' => 'M', 'price_modifier' => 0],
                    ['color' => 'Black', 'size' => 'L', 'price_modifier' => 0],
                    ['color' => 'Navy', 'size' => 'M', 'price_modifier' => 0],
                    ['color' => 'Navy', 'size' => 'L', 'price_modifier' => 0],
                ],
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'description' => 'Active noise cancellation, transparency mode, and spatial audio. Up to 6 hours of listening time.',
                'short_description' => 'Premium wireless earbuds',
                'sku' => 'EARBUDS-WIRELESS-PRO',
                'price' => 179.99,
                'compare_price' => 199.99,
                'cost_price' => 80.00,
                'inventory_quantity' => 75, // Simple product
                'type' => 'simple',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
                'tags' => ['audio', 'wireless', 'premium'],
                'category_names' => ['Accessories', 'Electronics'],
            ],
            [
                'name' => 'Designer Sneakers',
                'description' => 'Premium leather sneakers with comfortable cushioning. Available in multiple colors and sizes.',
                'short_description' => 'Premium leather sneakers',
                'sku' => 'SNEAKERS-DESIGNER',
                'price' => 159.99,
                'compare_price' => 199.99,
                'cost_price' => 70.00,
                'inventory_quantity' => 0,
                'type' => 'variable',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
                'tags' => ['shoes', 'leather', 'fashion'],
                'category_names' => ['Accessories', 'Men\'s Clothing'],
                'variants' => [
                    ['color' => 'White', 'size' => '8', 'price_modifier' => 0],
                    ['color' => 'White', 'size' => '9', 'price_modifier' => 0],
                    ['color' => 'White', 'size' => '10', 'price_modifier' => 0],
                    ['color' => 'White', 'size' => '11', 'price_modifier' => 0],
                    ['color' => 'Black', 'size' => '8', 'price_modifier' => 0],
                    ['color' => 'Black', 'size' => '9', 'price_modifier' => 0],
                    ['color' => 'Black', 'size' => '10', 'price_modifier' => 0],
                    ['color' => 'Navy', 'size' => '9', 'price_modifier' => 0],
                    ['color' => 'Navy', 'size' => '10', 'price_modifier' => 0],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $variants = $productData['variants'] ?? null;
            $categoryNames = $productData['category_names'];
            unset($productData['variants'], $productData['category_names']);

            // Use updateOrCreate to avoid duplicates
            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']], // Find by SKU
                $productData // Update with this data
            );

            // Sync categories (removes old, adds new)
            $categoriesToAttach = $categories->whereIn('name', $categoryNames)->pluck('id');
            $product->categories()->sync($categoriesToAttach);

            // Delete existing variants before creating new ones
            $product->variants()->delete();

            // Create variants if this is a variable product
            if ($variants) {
                $this->createVariants($product, $variants);
            }
        }
    }

    private function createAttributes()
    {
        echo "Creating attributes...\n";

        // Create Screen Size attribute FIRST
        $screenSizeAttribute = Attribute::updateOrCreate(
            ['name' => 'Screen Size'],
            [
                'type' => 'select',
                'display_type' => 'button',
                'is_variant' => true,
                'is_filterable' => true,
                'sort_order' => 0, // Show first
            ]
        );
        echo "Created Screen Size attribute with ID: {$screenSizeAttribute->id}\n";

        $screenSizes = ['13-inch', '15-inch', '16-inch'];
        foreach ($screenSizes as $screenSize) {
            $value = AttributeValue::updateOrCreate(
                [
                    'attribute_id' => $screenSizeAttribute->id,
                    'value' => $screenSize,
                ]
            );
            echo "Created screen size value: {$screenSize} with ID: {$value->id}\n";
        }

        // Create Color attribute
        $colorAttribute = Attribute::updateOrCreate(
            ['name' => 'Color'],
            [
                'type' => 'select',
                'display_type' => 'color_swatch',
                'is_variant' => true,
                'is_filterable' => true,
                'sort_order' => 1,
            ]
        );
        echo "Created Color attribute with ID: {$colorAttribute->id}\n";

        $colors = [
            ['value' => 'White', 'color_code' => '#FFFFFF'],
            ['value' => 'Black', 'color_code' => '#000000'],
            ['value' => 'Navy', 'color_code' => '#001F3F'],
            ['value' => 'Natural Titanium', 'color_code' => '#C0C0C0'],
            ['value' => 'Blue Titanium', 'color_code' => '#4169E1'],
            ['value' => 'White Titanium', 'color_code' => '#F8F8FF'],
            ['value' => 'Black Titanium', 'color_code' => '#2F2F2F'],
            ['value' => 'Midnight', 'color_code' => '#191D26'],
            ['value' => 'Silver', 'color_code' => '#E3E4E6'],
            ['value' => 'Space Gray', 'color_code' => '#7D7E80'],
            ['value' => 'Starlight', 'color_code' => '#F7F4ED'],
        ];

        foreach ($colors as $color) {
            AttributeValue::updateOrCreate(
                [
                    'attribute_id' => $colorAttribute->id,
                    'value' => $color['value'],
                ],
                ['color_code' => $color['color_code']]
            );
        }

        // Create Storage attribute
        $storageAttribute = Attribute::updateOrCreate(
            ['name' => 'Storage'],
            [
                'type' => 'select',
                'display_type' => 'button',
                'is_variant' => true,
                'is_filterable' => true,
                'sort_order' => 2,
            ]
        );
        echo "Created Storage attribute with ID: {$storageAttribute->id}\n";

        $storages = ['128GB', '256GB', '512GB', '1TB', '2TB'];
        foreach ($storages as $storage) {
            AttributeValue::updateOrCreate(
                [
                    'attribute_id' => $storageAttribute->id,
                    'value' => $storage,
                ]
            );
        }

        // Create Size attribute (for clothing)
        $sizeAttribute = Attribute::updateOrCreate(
            ['name' => 'Size'],
            [
                'type' => 'select',
                'display_type' => 'button',
                'is_variant' => true,
                'is_filterable' => true,
                'sort_order' => 3,
            ]
        );

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '7', '8', '9', '10', '11', '12'];
        foreach ($sizes as $size) {
            AttributeValue::updateOrCreate(
                [
                    'attribute_id' => $sizeAttribute->id,
                    'value' => $size,
                ]
            );
        }

        echo "Attributes creation completed!\n";
    }

    private function createVariants(Product $product, array $variantData)
    {
        foreach ($variantData as $index => $variant) {
            $variantPrice = $product->price + ($variant['price_modifier'] ?? 0);

            // Generate SKU
            $skuParts = [$product->sku];
            foreach ($variant as $key => $value) {
                if ($key !== 'price_modifier') {
                    $skuParts[] = strtoupper(str_replace([' ', 'GB', '-inch'], '', $value));
                }
            }
            $sku = implode('-', $skuParts);

            $productVariant = ProductVariant::updateOrCreate(
                ['sku' => $sku],
                [
                    'product_id' => $product->id,
                    'price' => $variantPrice,
                    'compare_price' => $product->compare_price ? $product->compare_price + ($variant['price_modifier'] ?? 0) : null,
                    'cost_price' => $product->cost_price,
                    'inventory_quantity' => rand(5, 25),
                    'position' => $index,
                    'is_active' => true,
                ]
            );

            // Attach attribute values - DEBUG VERSION
            $attributeValueIds = [];
            foreach ($variant as $attributeName => $attributeValue) {
                if ($attributeName === 'price_modifier') {
                    continue;
                }

                // Map variant keys to attribute names
                $searchAttributeName = match ($attributeName) {
                    'screen_size' => 'Screen Size',
                    'color' => 'Color',
                    'storage' => 'Storage',
                    'size' => 'Size',
                    default => ucfirst($attributeName)
                };

                echo "Looking for attribute: {$searchAttributeName} with value: {$attributeValue}\n";

                $attribute = Attribute::where('name', $searchAttributeName)->first();
                if (! $attribute) {
                    echo "ERROR: Attribute '{$searchAttributeName}' not found!\n";

                    continue;
                }

                $attributeValueModel = AttributeValue::where('attribute_id', $attribute->id)
                    ->where('value', $attributeValue)
                    ->first();

                if (! $attributeValueModel) {
                    echo "ERROR: AttributeValue '{$attributeValue}' not found for attribute '{$searchAttributeName}'!\n";

                    continue;
                }

                $attributeValueIds[] = $attributeValueModel->id;
                echo "SUCCESS: Found {$searchAttributeName}: {$attributeValue}\n";
            }

            $productVariant->attributeValues()->sync($attributeValueIds);
        }
    }
}
