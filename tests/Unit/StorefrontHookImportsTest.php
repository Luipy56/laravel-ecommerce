<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

class StorefrontHookImportsTest extends TestCase
{
    /**
     * Custom hooks that must be imported when invoked in storefront page components.
     *
     * @var array<string, string> hook name => relative import path
     */
    private const TRACKED_HOOKS = [
        'useDocumentMeta' => '../hooks/useDocumentMeta',
        'usePublicShopSettings' => '../hooks/usePublicShopSettings',
        'useKeyColors' => '../hooks/useKeyColors',
    ];

    public function test_storefront_pages_import_custom_hooks_they_use(): void
    {
        $pagesDir = base_path('resources/js/Pages');
        $pageFiles = glob($pagesDir.'/*.jsx') ?: [];

        $this->assertNotEmpty($pageFiles, 'Expected storefront page components under resources/js/Pages');

        foreach ($pageFiles as $pagePath) {
            $content = (string) file_get_contents($pagePath);
            $basename = basename($pagePath);

            foreach (self::TRACKED_HOOKS as $hook => $importPath) {
                if (! str_contains($content, $hook.'(')) {
                    continue;
                }

                $importPattern = '/import\s*\{[^}]*\b'.preg_quote($hook, '/').'\b[^}]*\}\s*from\s*[\'"]'.preg_quote($importPath, '/').'[\'"]/';
                $this->assertMatchesRegularExpression(
                    $importPattern,
                    $content,
                    sprintf('%s uses %s() but does not import it from %s', $basename, $hook, $importPath),
                );
            }
        }
    }
}
