<?php

namespace Database\Seeders;

use App\Models\Product\Attribute;
use App\Models\Product\AttributeValue;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Clear all existing data in the correct order (foreign keys)
        $this->command->info('Clearing existing data...');

        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear pivot tables first
        DB::table('product_categories')->truncate();
        DB::table('product_attributes')->truncate();
        DB::table('variant_attribute_values')->truncate();

        // Clear dependent tables
        ProductVariant::truncate();
        AttributeValue::truncate();

        // Clear main tables
        Product::truncate();
        Category::truncate();
        Attribute::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Data cleared successfully!');

        // Now seed fresh data
        $this->command->info('Seeding fresh data...');

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        $this->command->info('Seeding completed successfully!');
    }
}
