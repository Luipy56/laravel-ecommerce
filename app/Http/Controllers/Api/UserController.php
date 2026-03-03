<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Models\ClientContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $client = $request->user();
        $client->load([
            'contacts' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_primary')->orderBy('id'),
            'addresses' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_primary')->orderBy('id'),
        ]);

        $primary = $client->contacts->first(fn ($c) => $c->is_primary) ?? $client->contacts->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'login_email' => $client->login_email,
                'type' => $client->type,
                'identification' => $client->identification,
                'name' => $primary?->name,
                'surname' => $primary?->surname,
                'phone' => $primary?->phone,
                'address' => $client->addresses->first() ? [
                    'id' => $client->addresses->first()->id,
                    'street' => $client->addresses->first()->street,
                    'city' => $client->addresses->first()->city,
                    'province' => $client->addresses->first()->province,
                    'postal_code' => $client->addresses->first()->postal_code,
                ] : null,
                'addresses' => $client->addresses->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'label' => $a->label,
                    'street' => $a->street,
                    'city' => $a->city,
                    'province' => $a->province,
                    'postal_code' => $a->postal_code,
                    'is_primary' => $a->is_primary,
                ])->values()->all(),
                'contacts' => $client->contacts->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'surname' => $c->surname,
                    'phone' => $c->phone,
                    'phone2' => $c->phone2,
                    'email' => $c->email,
                    'is_primary' => $c->is_primary,
                ])->values()->all(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $client = $request->user();
        $validated = $request->validate([
            'identification' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ]);

        if (array_key_exists('identification', $validated)) {
            $client->update(['identification' => $validated['identification']]);
        }

        $primary = $client->contacts()->where('is_primary', true)->first();
        if ($primary) {
            $primary->update([
                'name' => $validated['name'],
                'surname' => $validated['surname'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);
        } else {
            $client->contacts()->create([
                'name' => $validated['name'],
                'surname' => $validated['surname'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $client->login_email,
                'is_primary' => true,
                'is_active' => true,
            ]);
        }

        if (! empty($validated['password'])) {
            $client->update(['password' => $validated['password']]);
        }

        return $this->show($request);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $client = $request->user();
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:shipping,installation,other'],
            'label' => ['nullable', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_primary' => ['boolean'],
        ]);

        if (! empty($validated['is_primary'])) {
            $client->addresses()->update(['is_primary' => false]);
        }

        $address = $client->addresses()->create([
            'type' => $validated['type'],
            'label' => $validated['label'] ?? null,
            'street' => $validated['street'],
            'city' => $validated['city'],
            'province' => $validated['province'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $this->formatAddress($address)], 201);
    }

    public function updateAddress(Request $request, int $id): JsonResponse
    {
        $address = ClientAddress::where('client_id', $request->user()->id)->where('is_active', true)->findOrFail($id);
        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:shipping,installation,other'],
            'label' => ['nullable', 'string', 'max:100'],
            'street' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_primary' => ['boolean'],
        ]);

        if (array_key_exists('is_primary', $validated) && ! empty($validated['is_primary'])) {
            $request->user()->addresses()->update(['is_primary' => false]);
        }
        $address->update($validated);

        return response()->json(['success' => true, 'data' => $this->formatAddress($address->fresh())]);
    }

    public function destroyAddress(Request $request, int $id): JsonResponse
    {
        $address = ClientAddress::where('client_id', $request->user()->id)->where('is_active', true)->findOrFail($id);
        $address->update(['is_active' => false]);

        return response()->json(['success' => true], 204);
    }

    public function storeContact(Request $request): JsonResponse
    {
        $client = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_primary' => ['boolean'],
        ]);

        $contact = $client->contacts()->create([
            'name' => $validated['name'],
            'surname' => $validated['surname'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $this->formatContact($contact)], 201);
    }

    public function updateContact(Request $request, int $id): JsonResponse
    {
        $contact = ClientContact::where('client_id', $request->user()->id)->where('is_active', true)->findOrFail($id);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_primary' => ['boolean'],
        ]);

        if (! empty($validated['is_primary'])) {
            $request->user()->contacts()->update(['is_primary' => false]);
        }
        $contact->update([
            'name' => $validated['name'] ?? $contact->name,
            'surname' => $validated['surname'] ?? $contact->surname,
            'phone' => $validated['phone'] ?? $contact->phone,
            'phone2' => $validated['phone2'] ?? $contact->phone2,
            'email' => $validated['email'] ?? $contact->email,
            'is_primary' => $validated['is_primary'] ?? $contact->is_primary,
        ]);

        return response()->json(['success' => true, 'data' => $this->formatContact($contact->fresh())]);
    }

    public function destroyContact(Request $request, int $id): JsonResponse
    {
        $contact = ClientContact::where('client_id', $request->user()->id)->where('is_active', true)->findOrFail($id);
        $contact->update(['is_active' => false]);

        return response()->json(['success' => true], 204);
    }

    private function formatAddress(ClientAddress $a): array
    {
        return [
            'id' => $a->id,
            'type' => $a->type,
            'label' => $a->label,
            'street' => $a->street,
            'city' => $a->city,
            'province' => $a->province,
            'postal_code' => $a->postal_code,
            'is_primary' => $a->is_primary,
        ];
    }

    private function formatContact(ClientContact $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'surname' => $c->surname,
            'phone' => $c->phone,
            'phone2' => $c->phone2,
            'email' => $c->email,
            'is_primary' => $c->is_primary,
        ];
    }
}
