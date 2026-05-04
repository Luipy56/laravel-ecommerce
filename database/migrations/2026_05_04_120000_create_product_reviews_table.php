<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()->comment('Links to the completed order; null when unverifiable');
            $table->unsignedTinyInteger('rating')->comment('Star rating 1–5');
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('pending')->comment('pending|approved|rejected');
            $table->string('admin_note', 500)->nullable()->comment('Optional note from admin (e.g. rejection reason)');
            $table->timestamps();

            $table->unique(['client_id', 'product_id'], 'product_reviews_client_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
