<?php

namespace Database\Seeders;

use App\Models\Product\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'children' => [
                    ['name' => 'Smartphones', 'description' => 'Mobile phones and accessories'],
                    ['name' => 'Laptops', 'description' => 'Computers and laptops'],
                    ['name' => 'Accessories', 'description' => 'Electronic accessories'],
                ],
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel',
                'children' => [
                    ['name' => 'Men\'s Clothing', 'description' => 'Clothing for men'],
                    ['name' => 'Women\'s Clothing', 'description' => 'Clothing for women'],
                    ['name' => 'Accessories', 'description' => 'Fashion accessories'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'is_active' => true,
            ]);

            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childData) {
                    Category::create([
                        'name' => $childData['name'],
                        'description' => $childData['description'],
                        'parent_id' => $category->id,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
