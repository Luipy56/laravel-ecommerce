<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class AdminHelpDraftParser
{
    /**
     * @return array{title: string, body: string}
     */
    public static function parseFile(string $draftPath): array
    {
        if (! File::exists($draftPath) || File::size($draftPath) === 0) {
            throw new RuntimeException('Draft file missing or empty.');
        }

        return self::parseContent(File::get($draftPath));
    }

    /**
     * @return array{title: string, body: string}
     */
    public static function parseContent(string $content): array
    {
        if (! str_starts_with($content, '---')) {
            throw new RuntimeException('Draft has no frontmatter.');
        }

        $parts = preg_split('/^---$/m', $content, 3);
        if ($parts === false || count($parts) < 3) {
            throw new RuntimeException('Draft frontmatter is malformed.');
        }

        $title = self::extractTitle($parts[1]);
        $body = ltrim($parts[2], "\n");

        if ($title === '' || $body === '') {
            throw new RuntimeException('Draft is missing title or body.');
        }

        if (mb_strlen($title) > 256) {
            $title = mb_substr($title, 0, 253).'...';
        }

        return ['title' => $title, 'body' => $body];
    }

    private static function extractTitle(string $frontmatter): string
    {
        foreach (preg_split('/\R/', $frontmatter) ?: [] as $line) {
            if (str_starts_with($line, 'title:')) {
                $title = trim(substr($line, strlen('title:')));
                $title = trim($title, " \t\"'");

                return str_replace("\n", ' ', $title);
            }
        }

        return '';
    }
}
