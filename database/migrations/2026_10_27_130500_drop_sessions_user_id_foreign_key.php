<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * sessions.user_id is shared by client (web) and admin guards. Constraining it
 * to clients breaks admin sessions whenever Auth::id() is an admin id that is
 * not a client row (or after clients are purged).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sessions DROP CONSTRAINT IF EXISTS sessions_user_id_foreign');

            return;
        }

        if ($driver === 'sqlite') {
            // SQLite often has no named FK enforcement for this; skip safely.
            return;
        }

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('clients')->nullOnDelete();
        });
    }
};
