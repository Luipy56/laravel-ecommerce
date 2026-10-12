<?php

namespace Tests\Unit;

use App\Services\AdminChat\AdminChatAgent;
use App\Services\AdminChat\AdminChatContextPack;
use App\Services\AdminChat\AdminChatHeuristic;
use App\Services\AdminChat\AdminChatLlmClient;
use App\Services\AdminChat\AdminChatToolService;
use Mockery;
use Tests\TestCase;

class AdminChatFallbackNoticeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_llm_failure_prepends_script_fallback_banner(): void
    {
        $pack = Mockery::mock(AdminChatContextPack::class);
        $pack->shouldReceive('load')->andReturn('# pack');

        $tools = Mockery::mock(AdminChatToolService::class);
        $tools->shouldReceive('run')->once()->andReturn([
            'ok' => true,
            'data' => [['id' => 1, 'code' => 'X', 'name' => 'Demo', 'qty_sold' => 2, 'revenue' => 10]],
            'meta' => ['total' => 1, 'returned' => 1],
        ]);

        $llm = Mockery::mock(AdminChatLlmClient::class);
        $llm->shouldReceive('complete')->once()->andReturn(null);
        $llm->shouldReceive('consumeLastFailure')->once()->andReturn('cursor_exit_1: usage limit');

        $agent = new AdminChatAgent($pack, $tools, new AdminChatHeuristic, $llm);
        $r = $agent->handle('productos más vendidos', 'cursor');

        $this->assertTrue($r['fallback'] ?? false);
        $this->assertSame('cursor_exit_1: usage limit', $r['fallback_reason'] ?? null);
        $this->assertStringStartsWith('[Fallback · script]', $r['reply']);
        $this->assertStringContainsString('motivo: cursor_exit_1: usage limit', $r['reply']);
        $this->assertStringContainsString('Productos más vendidos', $r['reply']);
        $this->assertStringContainsString('+fallback', $r['provider']);
    }
}
