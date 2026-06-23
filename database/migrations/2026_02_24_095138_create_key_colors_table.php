<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global catalog of key colors for products/packs that involve physical keys.
     */
    public function up(): void
    {
        Schema::create('key_colors', function (Blueprint $table) {
            $table->id();
            $table->string('rgb_code', 7)->comment('Hex RGB e.g. #C0C0C0');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_colors');
    }
};
