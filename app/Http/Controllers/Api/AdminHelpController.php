<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAdminHelpIssueJob;
use App\Services\AdminHelpIssueRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminHelpController extends Controller
{
    public function __construct(
        private readonly AdminHelpIssueRequestService $helpRequests,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $maxComment = (int) config('admin_help.comment_max_length', 4000);
        $maxTitle = (int) config('admin_help.title_max_length', 200);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:1', 'max:'.$maxComment],
            'title' => ['nullable', 'string', 'max:'.$maxTitle],
        ]);

        $admin = Auth::guard('admin')->user();
        if ($admin === null) {
            return response()->json(['success' => false, 'message' => __('Unauthorized')], 401);
        }

        $title = isset($validated['title']) ? trim($validated['title']) : '';
        if ($title === '') {
            $title = null;
        }

        $payload = [
            'id' => (string) Str::uuid(),
            'receivedAt' => now()->utc()->toIso8601String(),
            'submittedBy' => [
                'id' => $admin->id,
                'username' => $admin->username,
            ],
            'title' => $title,
            'comment' => $validated['comment'],
            'label' => (string) config('admin_help.fallback_label', 'waiting for human validation'),
            'meta' => [
                'userAgent' => $request->userAgent(),
                'remoteAddr' => $request->ip(),
                'source' => 'admin_help',
            ],
            'status' => 'pending',
        ];

        try {
            $this->helpRequests->storePending($payload);
        } catch (\Throwable $e) {
            Log::error('admin_help: failed to store request', [
                'exception' => $e::class,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Unable to submit the request. Please try again later.'),
            ], 500);
        }

        ProcessAdminHelpIssueJob::dispatch();

        return response()->json(['success' => true]);
    }
}
