<?php

namespace Tests\Unit;

use App\Services\AdminHelpDraftParser;
use RuntimeException;
use Tests\TestCase;

class AdminHelpDraftParserTest extends TestCase
{
    public function test_it_parses_title_and_body_from_frontmatter_draft(): void
    {
        $draft = <<<'MD'
---
title: "[admin/help] Orders export"
---

## Summary

Admin wants CSV export.

## Original submission

Add CSV export on the admin orders list.
MD;

        $parsed = AdminHelpDraftParser::parseContent($draft);

        $this->assertSame('[admin/help] Orders export', $parsed['title']);
        $this->assertStringContainsString('## Summary', $parsed['body']);
        $this->assertStringContainsString('Add CSV export on the admin orders list.', $parsed['body']);
    }

    public function test_it_strips_quotes_from_title(): void
    {
        $draft = <<<'MD'
---
title: "[admin/help] Bug in checkout"
---

Body text.
MD;

        $parsed = AdminHelpDraftParser::parseContent($draft);

        $this->assertSame('[admin/help] Bug in checkout', $parsed['title']);
    }

    public function test_it_throws_when_frontmatter_is_missing(): void
    {
        $this->expectException(RuntimeException::class);
        AdminHelpDraftParser::parseContent('# No frontmatter');
    }
}
