<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/**
 * Admin CRUD for admin users (list, create, show, update, soft delete).
 * Never expose password in responses.
 */
class AdminAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Admin::query()->orderBy('username');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $query->where('username', 'like', $term);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $admins = $query->get(['id', 'username', 'is_active', 'last_login_at', 'created_at']);

        $data = $admins->map(fn ($a) => [
            'id' => $a->id,
            'username' => $a->username,
            'is_active' => (bool) $a->is_active,
            'last_login_at' => $a->last_login_at?->toIso8601String(),
            'created_at' => $a->created_at?->toIso8601String(),
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'password' => ['required', 'string', 'max:255', Password::defaults()],
            'is_active' => ['boolean'],
        ]);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $admin = Admin::create($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'is_active' => (bool) $admin->is_active,
            ],
        ], 201);
    }

    public function show(Admin $admin): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'is_active' => (bool) $admin->is_active,
                'last_login_at' => $admin->last_login_at?->toIso8601String(),
                'created_at' => $admin->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, Admin $admin): JsonResponse
    {
        $rules = [
            'username' => ['required', 'string', 'max:255', 'unique:admins,username,' . $admin->id],
            'is_active' => ['boolean'],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'max:255', Password::defaults()];
        }
        $validated = $request->validate($rules);

        $admin->username = $validated['username'];
        $admin->is_active = $validated['is_active'] ?? $admin->is_active;
        if (! empty($validated['password'])) {
            $admin->password = $validated['password'];
        }
        $admin->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'is_active' => (bool) $admin->is_active,
            ],
        ]);
    }

    public function destroy(Admin $admin): JsonResponse
    {
        $admin->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
