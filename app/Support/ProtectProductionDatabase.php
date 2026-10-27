<?php

namespace App\Support;

use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Blocks destructive Artisan DB commands on production and staging.
 * Override only with SERRA_ALLOW_DESTRUCTIVE_DB=1 (operator emergency).
 */
final class ProtectProductionDatabase
{
    /** @var list<string> */
    public const BLOCKED_COMMANDS = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
        'db:seed',
    ];

    public static function environmentsProtected(): array
    {
        return ['production', 'staging'];
    }

    public static function allowDestructiveOverride(): bool
    {
        return filter_var(env('SERRA_ALLOW_DESTRUCTIVE_DB', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function shouldBlock(string $environment, string $commandName): bool
    {
        if (! in_array($environment, self::environmentsProtected(), true)) {
            return false;
        }

        if (self::allowDestructiveOverride()) {
            return false;
        }

        return in_array($commandName, self::BLOCKED_COMMANDS, true);
    }

    public static function handleCommandStarting(CommandStarting $event): void
    {
        $app = app();
        $env = $app->environment();
        $name = $event->command ?? '';

        if ($name === '' || ! self::shouldBlock($env, $name)) {
            return;
        }

        self::abort($env, $name, $event->input);
    }

    private static function abort(string $environment, string $commandName, ?InputInterface $input): void
    {
        $msg = sprintf(
            'Refusing `%s` on APP_ENV=%s: destructive database commands are blocked to protect live data. '.
            'Use `php artisan migrate` for schema changes. Override only with SERRA_ALLOW_DESTRUCTIVE_DB=1 after an explicit operator order.',
            $commandName,
            $environment,
        );

        if ($input !== null && $input->hasParameterOption(['-q', '--quiet'], true)) {
            fwrite(STDERR, $msg.PHP_EOL);
        } else {
            fwrite(STDERR, $msg.PHP_EOL);
        }

        exit(1);
    }
}
