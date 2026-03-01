<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * kind: cart = shopping cart; order = confirmed order. status only when kind=order.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('kind', 50)->comment('cart = shopping cart; order = confirmed order');
            $table->string('status', 50)->nullable()->comment('Only when kind=order; null when cart');
            $table->timestamp('order_date')->nullable()->comment('Null while cart; set on checkout');
            $table->timestamp('shipping_date')->nullable();
            $table->decimal('shipping_price', 10, 2)->nullable();
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
