<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route as RouteFacade;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

/**
 * Ensures every registered GET route responds without a 500 (server error).
 * Other statuses (401, 403, 404, 405, 422) are acceptable.
 *
 * Skipped entirely when pdo_sqlite is missing. For a check against your configured DB: php artisan routes:smoke
 */
#[RequiresPhpExtension('pdo_sqlite')]
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_distinct_get_routes_do_not_return_500(): void
    {
        $seen = [];

        foreach (RouteFacade::getRoutes() as $route) {
            if ($route->domain() !== null) {
                continue;
            }

            $methods = $route->methods();
            if (! in_array('GET', $methods, true)) {
                continue;
            }

            $uri = $route->uri();
            if (isset($seen[$uri])) {
                continue;
            }
            $seen[$uri] = true;

            if (str_starts_with($uri, 'storage/')) {
                continue;
            }

            $path = $this->expandRouteUri($uri);
            $url = '/'.ltrim($path, '/');

            $response = $this->get($url);

            $this->assertNotSame(
                500,
                $response->getStatusCode(),
                "GET {$url} (route uri: {$uri}) returned HTTP 500"
            );
        }
    }

    /**
     * Replace {param} placeholders so the router can match.
     */
    private function expandRouteUri(string $uri): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', function (array $m): string {
            $inner = $m[1];
            if ($inner === 'any?' || str_ends_with($inner, '?')) {
                return 'spa-smoke-path';
            }
            if ($inner === 'path') {
                return 'nonexistent-blob';
            }

            return '1';
        }, $uri) ?? $uri;
    }
}
