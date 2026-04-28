<?php

namespace Tests\Unit;

use App\Support\AdminIndexColumns;
use Tests\TestCase;

class AdminIndexColumnsTest extends TestCase
{
    public function test_normalize_orders_visible_columns_by_registry_order(): void
    {
        $out = AdminIndexColumns::normalize([
            'products' => ['name', 'code'],
        ]);
        $this->assertSame(['code', 'name'], $out['products']);
    }

    public function test_normalize_drops_unknown_column_ids(): void
    {
        $out = AdminIndexColumns::normalize([
            'products' => ['code', 'removed_in_future', 'name'],
        ]);
        $this->assertSame(['code', 'name'], $out['products']);
    }

    public function test_normalize_empty_list_falls_back_to_defaults_for_table(): void
    {
        $out = AdminIndexColumns::normalize([
            'products' => [],
        ]);
        $this->assertContains('code', $out['products']);
        $this->assertContains('stock', $out['products']);
    }

    public function test_normalize_null_uses_all_defaults(): void
    {
        $out = AdminIndexColumns::normalize(null);
        $this->assertArrayHasKey('orders', $out);
        $this->assertNotEmpty($out['orders']);
    }
}
