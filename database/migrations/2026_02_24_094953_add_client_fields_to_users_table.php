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
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('name', 255)->comment('First name');
            $table->string('surname', 255)->nullable()->comment('Last name');
            $table->string('phone', 50)->nullable();
            $table->string('phone2', 50)->nullable();
            $table->string('email', 255)->nullable()->comment('Contact email (optional)');
            $table->boolean('is_primary')->default(false)->comment('Main contact for this client');
            $table->boolean('is_active')->default(true)->comment('Soft delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};
