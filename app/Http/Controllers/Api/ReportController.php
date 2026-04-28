<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Services\Reporting\ReportSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportSummaryService $reportSummaryService,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $admin = $request->user('admin');
        if ($admin instanceof Admin) {
            return response()->json([
                'success' => true,
                'role' => 'admin',
                'data' => $this->reportSummaryService->buildForAdmin($request),
            ]);
        }

        $client = $request->user();
        if ($client instanceof Client) {
            if (! $client->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.verify_email_required'),
                    'code' => 'email_not_verified',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'role' => 'client',
                'data' => $this->reportSummaryService->buildForClient($request, $client),
            ]);
        }

        return response()->json(['success' => false, 'message' => __('auth.unauthenticated')], 401);
    }
}
