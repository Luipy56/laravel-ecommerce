<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * kind: cart = shopping cart; order = confirmed order; like = favorites/wishlist. status only when kind=order.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('kind', 50)->comment('cart = shopping cart; order = confirmed order; like = favorites/wishlist');
            $table->string('status', 50)->nullable()->comment('Only when kind=order; null when cart or like. Values: pending, awaiting_payment, awaiting_installation_price, in_transit, sent, installation_pending, installation_confirmed, returned');
            $table->timestamp('order_date')->nullable()->comment('Null while cart; set on checkout');
            $table->timestamp('shipping_date')->nullable();
            $table->decimal('shipping_price', 10, 2)->nullable()->comment('Flat shipping at checkout; default from shop_settings shipping_flat_eur; Order grand total uses this when set');
            $table->boolean('installation_requested')->default(false)->comment('Client wants installation for whole order; price set by admin');
            $table->decimal('installation_price', 10, 2)->nullable()->comment('Set by admin; one fee per order');
            $table->string('installation_status', 32)->nullable()->comment('pending, priced, rejected; null if installation not requested');
            $table->timestamps();
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
