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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');

            // Transaction details
            $table->enum('type', [
                'sale',           // Stock sold (negative)
                'return',         // Stock returned (positive)
                'restock',        // Manual restock (positive)
                'adjustment',     // Manual adjustment (positive/negative)
                'reservation',    // Stock reserved for pending order (negative)
                'release',         // Reserved stock released (positive)
            ]);

            $table->integer('quantity_change'); // Can be positive or negative
            $table->unsignedInteger('quantity_after'); // Stock level after this transaction
            $table->string('reference')->nullable(); // Order number, return number, etc.
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['product_id', 'created_at']);
            $table->index(['product_variant_id', 'created_at']);
            $table->index(['order_id']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
