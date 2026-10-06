<?php

namespace Tests\Unit;

use App\Services\AdminChat\AdminChatHeuristic;
use PHPUnit\Framework\TestCase;

class AdminChatHeuristicTest extends TestCase
{
    public function test_refuses_writes(): void
    {
        $h = new AdminChatHeuristic;
        $r = $h->suggest('crea un producto con este png');
        $this->assertSame('v1_read_only', $r['deny'] ?? null);
    }

    public function test_counts_clients(): void
    {
        $h = new AdminChatHeuristic;
        $r = $h->suggest('¿cuántos clientes hay?');
        $this->assertSame('explorer_aggregate', $r['name']);
        $this->assertSame('clients', $r['args']['table']);
    }

    public function test_invoice_order(): void
    {
        $h = new AdminChatHeuristic;
        $r = $h->suggest('factura del pedido 42');
        $this->assertSame('order_export_doc', $r['name']);
        $this->assertSame(42, $r['args']['id']);
        $this->assertSame('invoice', $r['args']['doc']);
    }

    public function test_identity_does_not_search_catalog(): void
    {
        $h = new AdminChatHeuristic;
        $r = $h->suggest('Hola, cómo te llamas y cuál es tu función?');
        $this->assertSame('identity', $r['reply'] ?? null);
        $this->assertArrayNotHasKey('name', $r);
    }

    public function test_vague_product_asks_for_code(): void
    {
        $h = new AdminChatHeuristic;
        $r = $h->suggest('¿existe un producto?');
        $this->assertSame('need_product_query', $r['reply'] ?? null);
    }

    public function test_product_code_search(): void
    {
        $h = new AdminChatHeuristic;
        $r = $h->suggest('busca producto evoK1');
        $this->assertSame('catalog_search', $r['name'] ?? null);
        $this->assertStringContainsString('evok1', mb_strtolower((string) ($r['args']['q'] ?? '')));
    }
}
