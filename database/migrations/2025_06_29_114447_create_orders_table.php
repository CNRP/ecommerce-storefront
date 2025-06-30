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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('restrict');

            // Order status - Changed from enum to string for flexibility
            $table->string('status', 50)->default('draft');

            // Financial data (all amounts in pence to avoid floating point issues)
            $table->unsignedInteger('subtotal_amount'); // Items total before tax/shipping
            $table->unsignedInteger('tax_amount'); // VAT amount
            $table->unsignedInteger('shipping_amount')->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('total_amount'); // Final total
            $table->string('currency', 3)->default('GBP');

            // VAT details
            $table->decimal('tax_rate', 5, 4)->default(0.20); // 20% UK VAT
            $table->boolean('tax_inclusive')->default(true); // UK prices include VAT

            // Payment information
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('payment_status', 50)->nullable()->default('pending');
            $table->timestamp('payment_confirmed_at')->nullable();

            // Customer information snapshot
            $table->json('customer_details'); // Name, email, phone at time of order
            $table->json('billing_address');
            $table->json('shipping_address');

            // Order metadata
            $table->json('metadata')->nullable(); // Custom data, tracking info, etc.
            $table->text('notes')->nullable(); // Internal notes
            $table->text('customer_notes')->nullable(); // Customer provided notes

            // Important dates
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Guest order access
            $table->string('guest_token')->nullable()->unique(); // For guest order tracking

            $table->timestamps();

            // Indexes
            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['customer_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['stripe_payment_intent_id']);
            $table->index(['guest_token']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
