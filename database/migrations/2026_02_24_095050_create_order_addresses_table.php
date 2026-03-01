<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * One row per address type per order (shipping, installation).
     */
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->string('type', 50)->comment('shipping, installation');
            $table->string('street', 255);
            $table->string('city', 100);
            $table->string('province', 100)->nullable()->comment('Provincia');
            $table->string('postal_code', 20)->nullable();
            $table->text('note')->nullable()->comment('e.g. If I\'m not there..., I\'ll be there at 3...');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
