<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128)->unique()->comment('Setting key');
            $table->json('value')->comment('JSON-encoded scalar or array');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
