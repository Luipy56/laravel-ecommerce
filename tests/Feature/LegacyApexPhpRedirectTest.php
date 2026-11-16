<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LegacyApexPhpRedirectTest extends TestCase
{
    public function test_index_php_redirects_to_home(): void
    {
        $this->get('/index.php')
            ->assertRedirect('/')
            ->assertStatus(301);
    }

    public function test_legacy_marketing_php_redirects_under_web(): void
    {
        $this->get('/cookies.php')
            ->assertRedirect('/web/cookies')
            ->assertStatus(301);

        $this->get('/blog-parts-d-un-pany.php')
            ->assertRedirect('/web/blog-parts-d-un-pany')
            ->assertStatus(301);
    }

    public function test_qui_som_php_redirects_to_nosaltres(): void
    {
        $this->get('/qui-som.php')
            ->assertRedirect('/web/nosaltres')
            ->assertStatus(301);
    }
}
