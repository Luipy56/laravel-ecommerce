<?php

namespace Tests\Unit;

use App\Support\ProductSearch;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    public function test_normalize_folds_case_and_accents(): void
    {
        $n = ProductSearch::normalize(' Tornillo Màxim ');
        $this->assertSame('tornillo maxim', $n);
    }

    public function test_search_variants_include_synonyms_for_catalan_term(): void
    {
        $variants = ProductSearch::searchVariants('cargol');
        $this->assertContains('cargol', $variants);
        $this->assertContains('tornillo', $variants);
    }

    public function test_search_variants_include_synonyms_for_spanish_term(): void
    {
        $variants = ProductSearch::searchVariants('martillo');
        $this->assertContains('martillo', $variants);
        $this->assertContains('martell', $variants);
    }

    #[DataProvider('likeEscapeProvider')]
    public function test_escape_like_escapes_metacharacters(string $in, string $expected): void
    {
        $this->assertSame($expected, ProductSearch::escapeLike($in));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function likeEscapeProvider(): array
    {
        return [
            'percent' => ['100%', '100\\%'],
            'underscore' => ['a_b', 'a\\_b'],
        ];
    }
}
