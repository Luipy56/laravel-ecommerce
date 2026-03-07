<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersonalizedSolutionAttachmentSeeder extends Seeder
{
    /**
     * Seeds personalized_solution_attachments. Each solution gets 2 images in personalized-solutions/{id}/ (same structure as real uploads).
     */
    public function run(): void
    {
        $fixtures = $this->getFixturePaths();
        if (empty($fixtures)) {
            return;
        }

        Storage::disk('uploads')->deleteDirectory('personalized-solutions');

        $rows = [];
        foreach ([1, 2, 3] as $solutionId) {
            $dir = 'personalized-solutions/' . $solutionId;
            $sortOrder = 1;
            foreach ($this->pickRandom($fixtures, 2) as $srcPath) {
                $filename = 'attachment-' . $sortOrder . '.jpg';
                $storagePath = $dir . '/' . $filename;
                Storage::disk('uploads')->put($storagePath, file_get_contents($srcPath));
                $rows[] = [
                    'personalized_solution_id' => $solutionId,
                    'storage_path' => $storagePath,
                    'original_filename' => $filename,
                    'size_bytes' => (int) filesize($srcPath),
                    'checksum' => null,
                    'content_type' => 'image/jpeg',
                    'description' => null,
                    'is_active' => true,
                ];
                $sortOrder++;
            }
        }

        if (! empty($rows)) {
            DB::table('personalized_solution_attachments')->insert($rows);
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
