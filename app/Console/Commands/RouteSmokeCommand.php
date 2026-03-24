<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteSmokeCommand extends Command
{
    protected $signature = 'routes:smoke {--details : Log every request status}';

    protected $description = 'Dispatch GET for each distinct route (parameters expanded) and fail if any response is HTTP 500';

    public function handle(Kernel $kernel): int
    {
        $seen = [];
        $failed = false;

        foreach (Route::getRoutes() as $route) {
            if ($route->domain() !== null) {
                continue;
            }
            if (! in_array('GET', $route->methods(), true)) {
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

            $path = '/'.ltrim($this->expandRouteUri($uri), '/');
            $request = Request::create($path, 'GET');
            $response = $kernel->handle($request);
            $code = $response->getStatusCode();
            $kernel->terminate($request, $response);

            if ($code === 500) {
                $this->error("GET {$path} (route: {$uri}) → {$code}");
                $failed = true;
            } elseif ($this->option('details')) {
                $this->line("GET {$path} → {$code}");
            }
        }

        if ($failed) {
            $this->error('One or more routes returned 500.');

            return self::FAILURE;
        }

        $this->info('All checked GET routes returned a non-500 status.');

        return self::SUCCESS;
    }

    private function expandRouteUri(string $uri): string
    {
        $expanded = preg_replace_callback('/\{([^}]+)\}/', function (array $m): string {
            $inner = $m[1];
            if ($inner === 'any?' || str_ends_with($inner, '?')) {
                return 'spa-smoke-path';
            }
            if ($inner === 'path') {
                return 'nonexistent-blob';
            }

            return '1';
        }, $uri);

        return $expanded ?? $uri;
    }
}
