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
            $table->string('code', 50)->nullable()->unique()->comment('Product code');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_installable')->default(false);
            $table->decimal('installation_price', 10, 2)->nullable()->comment('When is_installable=true; may vary by postal code in app');
            $table->boolean('is_extra_keys_available')->default(false)->comment('Product can have extra physical keys');
            $table->decimal('extra_key_unit_price', 10, 2)->nullable()->comment('Price per extra key when is_extra_keys_available=true');
            $table->boolean('is_featured')->default(false)->comment('Featured on homepage');
            $table->boolean('is_trending')->default(false)->comment('Trending product');
            $table->boolean('is_active')->default(true)->comment('Disable without deleting');
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
