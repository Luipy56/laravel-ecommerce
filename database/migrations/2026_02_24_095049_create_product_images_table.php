<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * References files in storage (FS/S3/MinIO); no blob in DB. Primary = sort_order 1 (or min).
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('storage_path', 1024)->comment('Path or key in storage');
            $table->string('filename', 255)->nullable()->comment('Original or assigned filename');
            $table->integer('size_bytes');
            $table->string('checksum', 64)->nullable()->comment('e.g. SHA-256 hash');
            $table->string('content_type', 100);
            $table->integer('sort_order')->default(0)->comment('Display order; primary when 1 or min');
            $table->boolean('is_active')->default(true)->comment('Soft delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
