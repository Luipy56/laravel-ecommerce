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
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('type', 50)->comment('Shipping, installation, etc.');
            $table->string('label', 100)->nullable()->comment('e.g. Head office, Warehouse');
            $table->string('street', 255);
            $table->string('city', 100);
            $table->string('province', 100)->nullable()->comment('Provincia');
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_active')->default(true)->comment('Soft delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
