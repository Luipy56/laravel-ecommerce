<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * One or more payment records per order (e.g. split payments).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->string('status', 32)->default('pending')->comment('pending|requires_action|processing|succeeded|failed|canceled|refunded');
            $table->string('gateway', 32)->nullable()->comment('stripe|redsys|revolut|null');
            $table->char('currency', 3)->default('EUR');
            $table->string('gateway_reference', 255)->nullable()->comment('Primary PSP id (e.g. PaymentIntent id)');
            $table->string('failure_code', 64)->nullable();
            $table->text('failure_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
