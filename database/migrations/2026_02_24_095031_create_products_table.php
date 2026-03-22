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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories');
            $table->foreignId('variant_group_id')->nullable()->constrained('product_variant_groups')->nullOnDelete();
            $table->string('code', 50)->nullable()->unique()->comment('Product code');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->comment('List/catalog price (storefront base before discount)');
            $table->decimal('discount_percent', 5, 2)->nullable()->comment('Optional 0–100; customer pays price * (1 - discount/100)');
            $table->decimal('purchase_price', 10, 2)->nullable()->comment('Cost price; admin only');
            $table->integer('stock')->default(0);
            $table->decimal('weight_kg', 10, 3)->nullable()->comment('Product weight in kilograms');
            $table->boolean('is_double_clutch')->default(false)->comment('Double clutch cylinder');
            $table->boolean('has_card')->default(false)->comment('Includes card (credential)');
            $table->string('security_level', 32)->nullable()->comment('standard|high|very_high');
            $table->string('competitor_url', 2048)->nullable()->comment('Optional competitor product URL');
            $table->boolean('is_extra_keys_available')->default(false)->comment('Product can have extra physical keys');
            $table->decimal('extra_key_unit_price', 10, 2)->nullable()->comment('Price per extra key when is_extra_keys_available=true');
            $table->boolean('is_featured')->default(false)->comment('Featured on homepage');
            $table->boolean('is_trending')->default(false)->comment('Trending product');
            $table->boolean('is_active')->default(true)->comment('Disable without deleting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
