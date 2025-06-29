<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('restrict');

            // Product snapshot at time of order (for historical accuracy)
            $table->string('product_name');
            $table->string('product_sku');
            $table->text('product_description')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('variant_sku')->nullable();
            $table->json('variant_attributes')->nullable(); // Size: Large, Color: Red, etc.

            // Pricing snapshot (amounts in pence)
            $table->unsignedInteger('unit_price'); // Price per item including VAT
            $table->unsignedInteger('unit_cost')->nullable(); // Cost price for profit calculation
            $table->unsignedInteger('compare_price')->nullable(); // Original price if discounted

            // Quantities and fulfillment
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_fulfilled')->default(0);
            $table->unsignedInteger('quantity_cancelled')->default(0);
            $table->unsignedInteger('quantity_refunded')->default(0);

            // Calculated totals (amounts in pence)
            $table->unsignedInteger('line_total'); // unit_price * quantity
            $table->unsignedInteger('tax_amount'); // VAT portion of line_total

            // Product images snapshot
            $table->string('product_image')->nullable();

            // Fulfillment details
            $table->json('fulfillment_details')->nullable(); // Tracking numbers, etc.
            $table->timestamp('fulfilled_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id']);
            $table->index(['product_id']);
            $table->index(['product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
