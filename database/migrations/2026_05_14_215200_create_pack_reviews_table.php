<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained('packs')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()->comment('Links to the completed order; null when unverifiable');
            $table->unsignedTinyInteger('rating')->comment('Star rating 1–5');
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('published')->comment('published|hidden');
            $table->string('admin_note', 500)->nullable()->comment('Optional internal admin note');
            $table->timestamps();

            $table->unique(['client_id', 'pack_id'], 'pack_reviews_client_pack_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_reviews');
    }
};
