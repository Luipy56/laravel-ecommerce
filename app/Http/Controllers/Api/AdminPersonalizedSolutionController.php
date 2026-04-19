<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PersonalizedSolutionResolvedMail;
use App\Models\PersonalizedSolution;
use App\Support\MailLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Admin CRUD for personalized solutions (client requests).
 * List, show, update (status/resolution), soft delete. No store (clients create via public API).
 */
class AdminPersonalizedSolutionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PersonalizedSolution::query()
            ->with(['client:id,login_email', 'attachments'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('problem_description', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('login_email', 'like', $term));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $solutions = $query->paginate($perPage);

        $data = $solutions->getCollection()->map(fn ($s) => [
            'id' => $s->id,
            'email' => $s->email,
            'phone' => $s->phone,
            'problem_description' => $s->problem_description ? \Illuminate\Support\Str::limit($s->problem_description, 120) : null,
            'status' => $s->status,
            'is_active' => (bool) $s->is_active,
            'created_at' => $s->created_at?->toIso8601String(),
            'client_id' => $s->client_id,
            'client_login_email' => $s->relationLoaded('client') && $s->client ? $s->client->login_email : null,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $solutions->currentPage(),
                'last_page' => $solutions->lastPage(),
                'per_page' => $solutions->perPage(),
                'total' => $solutions->total(),
            ],
        ]);
    }

    public function show(PersonalizedSolution $personalized_solution): JsonResponse
    {
        $personalized_solution->load(['client:id,login_email,identification', 'order:id,reference', 'attachments']);

        $attachments = $personalized_solution->attachments->map(fn ($a) => [
            'id' => $a->id,
            'original_filename' => $a->original_filename,
            'content_type' => $a->content_type,
            'size_bytes' => $a->size_bytes,
            'url' => $a->url,
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $personalized_solution->id,
                'client_id' => $personalized_solution->client_id,
                'client' => $personalized_solution->client ? [
                    'id' => $personalized_solution->client->id,
                    'login_email' => $personalized_solution->client->login_email,
                    'identification' => $personalized_solution->client->identification,
                ] : null,
                'order_id' => $personalized_solution->order_id,
                'order' => $personalized_solution->order ? [
                    'id' => $personalized_solution->order->id,
                    'reference' => $personalized_solution->order->reference,
                ] : null,
                'email' => $personalized_solution->email,
                'phone' => $personalized_solution->phone,
                'address_street' => $personalized_solution->address_street,
                'address_city' => $personalized_solution->address_city,
                'address_province' => $personalized_solution->address_province,
                'address_postal_code' => $personalized_solution->address_postal_code,
                'address_note' => $personalized_solution->address_note,
                'problem_description' => $personalized_solution->problem_description,
                'resolution' => $personalized_solution->resolution,
                'status' => $personalized_solution->status,
                'iterations_count' => (int) $personalized_solution->iterations_count,
                'improvement_feedback' => $personalized_solution->improvement_feedback,
                'improvement_feedback_at' => $personalized_solution->improvement_feedback_at?->toIso8601String(),
                'portal_url' => $personalized_solution->public_token ? $personalized_solution->portalUrl() : null,
                'is_active' => (bool) $personalized_solution->is_active,
                'created_at' => $personalized_solution->created_at?->toIso8601String(),
                'updated_at' => $personalized_solution->updated_at?->toIso8601String(),
                'attachments' => $attachments,
            ],
        ]);
    }

    public function notifyResolution(Request $request, PersonalizedSolution $personalized_solution): JsonResponse
    {
        $email = $personalized_solution->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => __('client_portal.admin_notify_no_email'),
            ], 422);
        }

        $locale = MailLocale::resolve($request->getPreferredLanguage(['ca', 'es']));
        Mail::to($email)->locale($locale)->send(new PersonalizedSolutionResolvedMail($personalized_solution->fresh()));

        return response()->json(['success' => true]);
    }

    public function update(Request $request, PersonalizedSolution $personalized_solution): JsonResponse
    {
        $previousStatus = $personalized_solution->status;

        $request->merge([
            'client_id' => $request->client_id === '' ? null : $request->client_id,
            'order_id' => $request->order_id === '' ? null : $request->order_id,
        ]);
        $validated = $request->validate([
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_postal_code' => ['required', 'string', 'max:20'],
            'address_note' => ['nullable', 'string', 'max:1000'],
            'problem_description' => ['nullable', 'string', 'max:5000'],
            'resolution' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', 'in:' . implode(',', [
                PersonalizedSolution::STATUS_PENDING_REVIEW,
                PersonalizedSolution::STATUS_REVIEWED,
                PersonalizedSolution::STATUS_CLIENT_CONTACTED,
                PersonalizedSolution::STATUS_REJECTED,
                PersonalizedSolution::STATUS_COMPLETED,
            ])],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'is_active' => ['boolean'],
            'clear_improvement_feedback' => ['boolean'],
        ]);

        $personalized_solution->email = $validated['email'] ?? null;
        $personalized_solution->phone = $validated['phone'] ?? null;
        $personalized_solution->address_street = $validated['address_street'] ?? null;
        $personalized_solution->address_city = $validated['address_city'] ?? null;
        $personalized_solution->address_province = $validated['address_province'] ?? null;
        $personalized_solution->address_postal_code = $validated['address_postal_code'] ?? null;
        $personalized_solution->address_note = $validated['address_note'] ?? null;
        $personalized_solution->problem_description = $validated['problem_description'] ?? null;
        $personalized_solution->resolution = $validated['resolution'] ?? null;
        $personalized_solution->status = $validated['status'];
        $personalized_solution->client_id = $validated['client_id'] ?? null;
        $personalized_solution->order_id = $validated['order_id'] ?? null;
        if (array_key_exists('is_active', $validated)) {
            $personalized_solution->is_active = $validated['is_active'];
        }
        if ($request->boolean('clear_improvement_feedback')) {
            $personalized_solution->improvement_feedback = null;
            $personalized_solution->improvement_feedback_at = null;
        }
        $personalized_solution->save();

        $fresh = $personalized_solution->fresh();
        if ($fresh
            && $previousStatus !== PersonalizedSolution::STATUS_COMPLETED
            && $fresh->status === PersonalizedSolution::STATUS_COMPLETED
            && $fresh->email
            && filter_var($fresh->email, FILTER_VALIDATE_EMAIL)) {
            $locale = MailLocale::resolve($request->getPreferredLanguage(['ca', 'es']));
            Mail::to($fresh->email)->locale($locale)->send(new PersonalizedSolutionResolvedMail($fresh));
        }

        return response()->json([
            'success' => true,
            'data' => ['id' => $personalized_solution->id],
        ]);
    }

    public function destroy(PersonalizedSolution $personalized_solution): JsonResponse
    {
        $personalized_solution->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
