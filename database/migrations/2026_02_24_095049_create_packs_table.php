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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_trending')->default(false)->comment('Trending pack');
            $table->boolean('is_active')->default(true)->comment('Disable without deleting');
            $table->boolean('is_installable')->default(false)->comment('Pack can include installation');
            $table->decimal('installation_price', 10, 2)->nullable()->comment('Installation price when is_installable');
            $table->boolean('contains_keys')->default(false)->comment('Pack contains keys (e.g. 3 locks); client can choose at cart: all same key or all different');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packs');
    }
};
