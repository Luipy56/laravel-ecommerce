<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login_email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['login_email' => $validated['login_email'], 'password' => $validated['password']], $request->boolean('remember'))) {
            return response()->json([
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => ['login_email' => [__('auth.failed')]],
            ], 422);
        }

        $request->session()->regenerate();
        $client = Auth::user();
        $client->load(['contacts' => fn ($q) => $q->where('is_primary', true)]);
        $primary = $client->contacts->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'login_email' => $client->login_email,
                'type' => $client->type,
                'identification' => $client->identification,
                'name' => $primary?->name,
                'surname' => $primary?->surname,
                'email_verified' => $client->hasVerifiedEmail(),
                'email_verified_at' => $client->email_verified_at?->toIso8601String(),
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:person,company'],
            'identification' => ['nullable', 'string', 'max:20', 'unique:clients,identification'],
            'login_email' => ['required', 'string', 'email', 'max:255', 'unique:clients,login_email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_postal_code' => ['required', 'string', 'regex:/^\d{1,20}$/'],
        ]);

        $client = Client::create([
            'type' => $validated['type'],
            'identification' => $validated['identification'] ?? null,
            'login_email' => $validated['login_email'],
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        $client->contacts()->create([
            'name' => $validated['name'],
            'surname' => $validated['surname'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['login_email'],
            'is_primary' => true,
            'is_active' => true,
        ]);

        if (! empty($validated['address_street']) || ! empty($validated['address_city'])) {
            $client->addresses()->create([
                'type' => 'shipping',
                'street' => $validated['address_street'] ?? '',
                'city' => $validated['address_city'] ?? '',
                'province' => $validated['address_province'] ?? null,
                'postal_code' => $validated['address_postal_code'] ?? null,
                'is_active' => true,
            ]);
        }

        Auth::login($client);
        $request->session()->regenerate();

        $client->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'login_email' => $client->login_email,
                'type' => $client->type,
                'identification' => $client->identification,
                'name' => $validated['name'],
                'surname' => $validated['surname'] ?? null,
                'email_verified' => false,
                'email_verified_at' => null,
            ],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function user(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => true, 'data' => null]);
        }

        $client = $request->user();
        $client->load(['contacts' => fn ($q) => $q->where('is_primary', true), 'addresses']);

        $primary = $client->contacts->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'login_email' => $client->login_email,
                'type' => $client->type,
                'identification' => $client->identification,
                'name' => $primary?->name,
                'surname' => $primary?->surname,
                'phone' => $primary?->phone ?? $primary?->client?->contacts->first()?->phone,
                'address' => $client->addresses->first() ? [
                    'street' => $client->addresses->first()->street,
                    'city' => $client->addresses->first()->city,
                    'province' => $client->addresses->first()->province,
                    'postal_code' => $client->addresses->first()->postal_code,
                ] : null,
                'email_verified' => $client->hasVerifiedEmail(),
                'email_verified_at' => $client->email_verified_at?->toIso8601String(),
            ],
        ]);
    }
}
