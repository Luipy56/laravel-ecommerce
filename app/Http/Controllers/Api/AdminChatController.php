<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminChat\AdminChatAgent;
use App\Services\AdminChat\AdminChatDownloadStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminChatController extends Controller
{
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'default_provider' => config('admin_chat.default_provider'),
                'providers' => ['ollama', 'cursor', 'heuristic'],
                'scope' => 'read_export_v1',
            ],
        ]);
    }

    public function message(Request $request, AdminChatAgent $agent): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:'.(int) config('admin_chat.max_message_chars', 4000)],
            'provider' => ['nullable', 'string', 'in:ollama,cursor,heuristic'],
            'history' => ['nullable', 'array', 'max:16'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $result = $agent->handle(
            $validated['message'],
            $validated['provider'] ?? (string) config('admin_chat.default_provider'),
            $validated['history'] ?? [],
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function download(string $token, AdminChatDownloadStore $store): Response
    {
        $file = $store->get($token);
        if ($file === null) {
            abort(404);
        }

        return response($file['body'], 200, [
            'Content-Type' => $file['content_type'],
            'Content-Disposition' => 'inline; filename="'.$file['filename'].'"',
        ]);
    }
}
