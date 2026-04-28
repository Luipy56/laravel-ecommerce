<?php

namespace App\Http\Controllers\Api;

use App\Events\PersonalizedSolutionSubmitted;
use App\Http\Controllers\Controller;
use App\Models\PersonalizedSolution;
use App\Models\ShopSetting;
use App\Support\MailLocale;
use App\Support\ValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PersonalizedSolutionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! ShopSetting::get(ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS, true)) {
            return response()->json([
                'success' => false,
                'message' => __('shop.personalized_solutions_disabled'),
            ], 422);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'max:255', ValidationRules::emailDns()],
            'phone' => ['nullable', 'string', 'max:50'],
            'problem_description' => ['required', 'string', 'max:5000'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_postal_code' => ['required', 'string', 'regex:/^\d{1,20}$/'],
            'address_note' => ['nullable', 'string', 'max:1000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'], // 10MB
        ]);

        $dedupKey = 'ps-store:'.hash('sha256', (string) $request->ip().'|'
            .strtolower($validated['email']).'|'
            .substr($validated['problem_description'], 0, 200));
        if (! Cache::add($dedupKey, 1, 8)) {
            return response()->json([
                'success' => false,
                'message' => __('shop.personalized_solution_too_soon'),
            ], 429);
        }

        $solution = new PersonalizedSolution([
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'problem_description' => $validated['problem_description'],
            'address_street' => $validated['address_street'] ?? null,
            'address_city' => $validated['address_city'] ?? null,
            'address_province' => $validated['address_province'] ?? null,
            'address_postal_code' => $validated['address_postal_code'] ?? null,
            'address_note' => $validated['address_note'] ?? null,
            'status' => PersonalizedSolution::STATUS_PENDING_REVIEW,
            'is_active' => true,
        ]);
        if ($request->user()) {
            $solution->client_id = $request->user()->id;
        }
        $solution->save();

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('personalized-solutions/'.$solution->id, 'uploads');
                \App\Models\PersonalizedSolutionAttachment::create([
                    'personalized_solution_id' => $solution->id,
                    'storage_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'size_bytes' => $file->getSize(),
                    'content_type' => $file->getMimeType(),
                    'is_active' => true,
                ]);
            }
        }

        PersonalizedSolutionSubmitted::dispatch(
            $solution->fresh(),
            MailLocale::resolve($request->getPreferredLanguage(config('app.available_locales', ['ca', 'es', 'en']))),
        );

        $solution->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $solution->id,
                'status' => $solution->status,
                'public_token' => $solution->public_token,
                'client_portal_path' => '/client/personalized-solutions/'.$solution->public_token,
            ],
        ], 201);
    }
}
