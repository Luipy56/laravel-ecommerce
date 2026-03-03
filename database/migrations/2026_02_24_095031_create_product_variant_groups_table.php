<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Optional grouping of products as siblings (e.g. same screw 30mm, 40mm, 50mm).
     */
    public function up(): void
    {
        Schema::create('product_variant_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable()->comment('Optional display name for the variant group');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_groups');
    }
};
