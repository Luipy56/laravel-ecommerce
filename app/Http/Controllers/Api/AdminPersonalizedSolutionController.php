<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalizedSolution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $solutions = $query->get();

        $data = $solutions->map(fn ($s) => [
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
                'is_active' => (bool) $personalized_solution->is_active,
                'created_at' => $personalized_solution->created_at?->toIso8601String(),
                'updated_at' => $personalized_solution->updated_at?->toIso8601String(),
                'attachments' => $attachments,
            ],
        ]);
    }

    public function update(Request $request, PersonalizedSolution $personalized_solution): JsonResponse
    {
        $validated = $request->validate([
            'resolution' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', 'in:' . implode(',', [
                PersonalizedSolution::STATUS_PENDING_REVIEW,
                PersonalizedSolution::STATUS_REVIEWED,
                PersonalizedSolution::STATUS_CLIENT_CONTACTED,
            ])],
            'is_active' => ['boolean'],
        ]);

        $personalized_solution->resolution = $validated['resolution'] ?? $personalized_solution->resolution;
        $personalized_solution->status = $validated['status'];
        if (array_key_exists('is_active', $validated)) {
            $personalized_solution->is_active = $validated['is_active'];
        }
        $personalized_solution->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $personalized_solution->id,
                'status' => $personalized_solution->status,
                'resolution' => $personalized_solution->resolution,
                'is_active' => (bool) $personalized_solution->is_active,
            ],
        ]);
    }

    public function destroy(PersonalizedSolution $personalized_solution): JsonResponse
    {
        $personalized_solution->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
