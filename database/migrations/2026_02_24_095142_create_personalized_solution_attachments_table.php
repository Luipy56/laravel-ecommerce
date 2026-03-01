<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * References files in storage (FS/S3/MinIO); no blob in DB.
     */
    public function up(): void
    {
        Schema::create('personalized_solution_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personalized_solution_id');
            $table->foreign('personalized_solution_id', 'ps_att_solution_fk')->references('id')->on('personalized_solutions');
            $table->string('storage_path', 1024)->comment('Path or key in storage');
            $table->string('original_filename', 255)->nullable();
            $table->integer('size_bytes');
            $table->string('checksum', 64)->nullable()->comment('e.g. SHA-256 hash');
            $table->string('content_type', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->comment('Soft delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personalized_solution_attachments');
    }
};
