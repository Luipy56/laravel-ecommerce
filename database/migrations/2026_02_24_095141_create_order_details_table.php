<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Line items of an order (product or pack, quantity, price). One of product_id or pack_id must be set.
     */
    public function up(): void
    {
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('pack_id')->nullable()->constrained('packs')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->nullable()->comment('Set at payment time from current product/pack price; null while in cart');
            $table->decimal('offer', 10, 2)->nullable()->comment('Discount amount applied');
            $table->boolean('is_installation_requested')->default(false);
            $table->decimal('installation_price', 10, 2)->nullable()->comment('Optional installation price for this line');
            $table->integer('extra_keys_qty')->default(0)->comment('Number of extra keys requested');
            $table->decimal('extra_key_unit_price', 10, 2)->nullable()->comment('Price per extra key at order time');
            $table->boolean('is_included')->default(true)->comment('If false, line is excluded from total (e.g. saved for later)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
