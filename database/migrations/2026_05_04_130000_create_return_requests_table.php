<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RMA (Return Merchandise Authorization) requests.
     * status: pending_review | approved | rejected | refunded | cancelled
     */
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('status', 32)->default('pending_review')->comment('pending_review | approved | rejected | refunded | cancelled');
            $table->text('reason');
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable()->comment('Actual refunded amount; set when refund is issued');
            $table->timestamp('refunded_at')->nullable();
            $table->string('gateway_refund_reference', 255)->nullable()->comment('PSP refund id (e.g. Stripe re_xxx)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
