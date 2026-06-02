<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GoogleOAuthSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_table_has_google_oauth_columns_after_migrations(): void
    {
        $this->assertTrue(Schema::hasColumn('clients', 'google_sub'));
        $this->assertTrue(Schema::hasColumn('clients', 'password'));
    }
}
