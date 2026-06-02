<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PackImageSeeder extends Seeder
{
    private const MIME_MAP = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    /**
     * Seeds pack_images. Each pack gets 2 images in packs/{id}/ (same structure as real uploads).
     */
    public function run(): void
    {
        $fixtures = $this->getFixturePaths();
        if (empty($fixtures)) {
            return;
        }

        Storage::disk('uploads')->deleteDirectory('packs');

        $rows = [];
        foreach ([1, 2, 3] as $packId) {
            $dir = 'packs/' . $packId;
            $sortOrder = 1;
            foreach ($this->pickRandom($fixtures, 2) as $srcPath) {
                $ext = strtolower(pathinfo($srcPath, PATHINFO_EXTENSION));
                $mime = self::MIME_MAP[$ext] ?? 'image/jpeg';
                $filename = 'image-' . $sortOrder . '.' . $ext;
                $storagePath = $dir . '/' . $filename;
                Storage::disk('uploads')->put($storagePath, file_get_contents($srcPath));
                $rows[] = [
                    'pack_id' => $packId,
                    'storage_path' => $storagePath,
                    'filename' => $filename,
                    'size_bytes' => (int) filesize($srcPath),
                    'checksum' => null,
                    'content_type' => $mime,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ];
                $sortOrder++;
            }
        }

        if (! empty($rows)) {
            DB::table('pack_images')->insert($rows);
        }
    }

    /** @return array<string> */
    private function getFixturePaths(): array
    {
        $dir = database_path('seeders/fixtures/images');
        $paths = [];
        foreach (glob($dir . '/seeder-*') as $p) {
            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
            if (is_file($p) && isset(self::MIME_MAP[$ext])) {
                $paths[] = $p;
            }
        }
        return $paths;
    }

    /** @return array<string> */
    private function pickRandom(array $paths, int $count): array
    {
        if (empty($paths)) {
            return [];
        }
        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $picked[] = $paths[array_rand($paths)];
        }
        return $picked;
    }
}
