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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Color, Size, Storage, etc.
            $table->string('slug')->unique();
            $table->enum('type', ['text', 'number', 'select', 'multiselect', 'boolean']);
            $table->enum('display_type', ['color_swatch', 'button', 'dropdown', 'radio'])->default('button');
            $table->json('options')->nullable(); // For select types
            $table->boolean('is_required')->default(false);
            $table->boolean('is_variant')->default(false); // Creates variants
            $table->boolean('is_filterable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
