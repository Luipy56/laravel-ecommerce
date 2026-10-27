<?php

namespace Tests\Unit;

use App\Support\ProtectProductionDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProtectProductionDatabaseTest extends TestCase
{
    #[DataProvider('blockMatrix')]
    public function test_should_block_matrix(string $env, string $command, bool $expectBlock): void
    {
        $this->assertSame(
            $expectBlock,
            ProtectProductionDatabase::shouldBlock($env, $command),
        );
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function blockMatrix(): array
    {
        return [
            'prod fresh' => ['production', 'migrate:fresh', true],
            'prod wipe' => ['production', 'db:wipe', true],
            'prod seed' => ['production', 'db:seed', true],
            'prod refresh' => ['production', 'migrate:refresh', true],
            'prod reset' => ['production', 'migrate:reset', true],
            'prod migrate ok' => ['production', 'migrate', false],
            'staging fresh' => ['staging', 'migrate:fresh', true],
            'local fresh ok' => ['local', 'migrate:fresh', false],
            'testing fresh ok' => ['testing', 'migrate:fresh', false],
        ];
    }
}
