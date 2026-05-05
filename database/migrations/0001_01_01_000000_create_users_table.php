<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Account (person or company). Use client_contacts for names/phones, client_addresses for addresses.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Search (pg_trgm GIN), case-insensitive login email (citext), accent folding in SQL when needed (unaccent).
            DB::unprepared(<<<'SQL'
                CREATE EXTENSION IF NOT EXISTS pg_trgm;
                CREATE EXTENSION IF NOT EXISTS citext;
                CREATE EXTENSION IF NOT EXISTS unaccent;
            SQL);
        }

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->comment('Person vs company');
            $table->text('identification')->nullable()->comment('ID document (DNI, CIF or NIE); encrypted at rest. Uniqueness enforced at application layer.');
            $table->string('login_email', 255)->unique()->comment('Email for authentication');
            $table->string('password', 255);
            $table->timestamp('email_verified_at')->nullable()->comment('Email verification timestamp');
            $table->rememberToken();
            $table->boolean('is_active')->default(true)->comment('Soft delete');
            $table->timestamps();
        });

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE clients ALTER COLUMN login_email TYPE citext USING login_email::citext');
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE password_reset_tokens ALTER COLUMN email TYPE citext USING email::citext');
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('clients');
    }
};
