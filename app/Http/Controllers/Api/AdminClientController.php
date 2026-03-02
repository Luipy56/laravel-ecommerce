<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin: list clients and show client detail (contacts, addresses).
 * Never expose password.
 */
class AdminClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query()
            ->withCount(['contacts', 'addresses'])
            ->with(['contacts' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_primary')->orderBy('id')])
            ->orderBy('login_email');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($term) {
                $q->where('login_email', 'like', $term)
                    ->orWhere('identification', 'like', $term)
                    ->orWhereHas('contacts', fn ($sub) => $sub->where('is_active', true)
                        ->where(function ($s) use ($term) {
                            $s->where('name', 'like', $term)
                                ->orWhere('surname', 'like', $term)
                                ->orWhere('email', 'like', $term)
                                ->orWhere('phone', 'like', $term);
                        }));
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $clients = $query->get();

        $data = $clients->map(function ($c) {
            $primary = $c->contacts->first(fn ($x) => $x->is_primary) ?? $c->contacts->first();

            return [
                'id' => $c->id,
                'type' => $c->type,
                'identification' => $c->identification,
                'login_email' => $c->login_email,
                'is_active' => (bool) $c->is_active,
                'contacts_count' => $c->contacts_count ?? 0,
                'addresses_count' => $c->addresses_count ?? 0,
                'primary_contact_name' => $primary ? trim($primary->name . ' ' . ($primary->surname ?? '')) : null,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load([
            'contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('id'),
            'addresses' => fn ($q) => $q->orderBy('type')->orderBy('id'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'type' => $client->type,
                'identification' => $client->identification,
                'login_email' => $client->login_email,
                'is_active' => (bool) $client->is_active,
                'contacts' => $client->contacts->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'surname' => $c->surname,
                    'phone' => $c->phone,
                    'phone2' => $c->phone2,
                    'email' => $c->email,
                    'is_primary' => (bool) $c->is_primary,
                    'is_active' => (bool) $c->is_active,
                ])->values()->all(),
                'addresses' => $client->addresses->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'label' => $a->label,
                    'street' => $a->street,
                    'city' => $a->city,
                    'province' => $a->province,
                    'postal_code' => $a->postal_code,
                    'is_active' => (bool) $a->is_active,
                ])->values()->all(),
            ],
        ]);
    }
}
