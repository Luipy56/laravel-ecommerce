<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

/**
 * Admin-only metadata for the About page (reads repository CHANGELOG.md).
 */
class AdminAboutController extends Controller
{
    public function changelog(): JsonResponse
    {
        $path = base_path('CHANGELOG.md');
        if (! File::isFile($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Changelog file not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'markdown' => File::get($path),
            ],
        ]);
    }
}
