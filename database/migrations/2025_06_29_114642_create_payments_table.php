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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('stripe_customer_id')->nullable();

            // Payment details
            $table->enum('type', ['payment', 'refund'])->default('payment');

            // FIXED: Changed from enum to string to support all Stripe status values
            $table->string('status', 50)->default('pending'); // Changed from enum to string

            // Amounts (in pence)
            $table->unsignedInteger('amount');
            $table->unsignedInteger('amount_received')->nullable(); // Actual amount received
            $table->unsignedInteger('application_fee')->nullable(); // Stripe fees
            $table->string('currency', 3)->default('GBP');

            // Payment method details (for display only - no sensitive data)
            $table->string('payment_method_type')->nullable(); // card, bank_transfer, etc.
            $table->json('payment_method_details')->nullable(); // Last 4 digits, brand, etc.

            // Stripe webhook data
            $table->json('stripe_data')->nullable(); // Full Stripe response for debugging
            $table->timestamp('processed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->text('failure_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id']);
            $table->index(['stripe_payment_intent_id']);
            $table->index(['status']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
