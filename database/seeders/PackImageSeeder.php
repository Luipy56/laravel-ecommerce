<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PackImageSeeder extends Seeder
{
    /**
     * Seeds pack_images. Each pack gets 2 images in packs/{id}/ (same structure as real uploads).
     */
    public function run(): void
    {
        $fixtures = $this->getFixturePaths();
        if (empty($fixtures)) {
            return;
        }

        Storage::disk('public')->deleteDirectory('packs');

        $rows = [];
        foreach ([1, 2, 3] as $packId) {
            $dir = 'packs/' . $packId;
            $sortOrder = 1;
            foreach ($this->pickRandom($fixtures, 2) as $srcPath) {
                $filename = 'image-' . $sortOrder . '.jpg';
                $storagePath = $dir . '/' . $filename;
                Storage::disk('public')->put($storagePath, file_get_contents($srcPath));
                $rows[] = [
                    'pack_id' => $packId,
                    'storage_path' => $storagePath,
                    'filename' => $filename,
                    'size_bytes' => (int) filesize($srcPath),
                    'checksum' => null,
                    'content_type' => 'image/jpeg',
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
        for ($i = 1; $i <= 10; $i++) {
            $p = $dir . '/seeder-' . $i . '.jpg';
            if (is_file($p)) {
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
