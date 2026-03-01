<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Can be independent of client and order (client_id, order_id null).
     */
    public function up(): void
    {
        Schema::create('personalized_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address_street', 255)->nullable()->comment('Same structure as ORDERS shipping address');
            $table->string('address_city', 100)->nullable();
            $table->string('address_province', 100)->nullable()->comment('Provincia');
            $table->string('address_postal_code', 20)->nullable();
            $table->text('address_note')->nullable()->comment('e.g. If I\'m not there..., I\'ll be there at 3...');
            $table->text('problem_description')->nullable();
            $table->text('resolution')->nullable()->comment('Admin resolution / quote');
            $table->string('status', 50);
            $table->boolean('is_active')->default(true)->comment('Soft delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personalized_solutions');
    }
};
