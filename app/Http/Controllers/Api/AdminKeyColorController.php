<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KeyColor;
use App\Support\CatalogLocale;
use App\Support\CatalogTranslationSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminKeyColorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KeyColor::query()->with('translations')->orderBySort();

        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->trim().'%';
            $loc = CatalogLocale::normalize(app()->getLocale());
            $query->where(function ($q) use ($term, $loc) {
                $q->where('rgb_code', 'like', $term)
                    ->orWhereHas('translations', fn ($t) => $t->where('locale', $loc)->where('name', 'like', $term));
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $colors = $query->paginate($perPage);

        $data = collect($colors->items())->map(fn (KeyColor $c) => $this->serialize($c))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $colors->currentPage(),
                'last_page' => $colors->lastPage(),
                'per_page' => $colors->perPage(),
                'total' => $colors->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rgb_code' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'name' => ['required', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
        ]);

        $keyColor = KeyColor::create([
            'rgb_code' => strtoupper($validated['rgb_code']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $this->syncTranslations($keyColor, $validated['name'], $request->input('translations'));
        $keyColor->load('translations');

        return response()->json(['success' => true, 'data' => $this->serialize($keyColor, true)], 201);
    }

    public function show(KeyColor $keyColor): JsonResponse
    {
        $keyColor->load('translations');

        return response()->json(['success' => true, 'data' => $this->serialize($keyColor, true)]);
    }

    public function update(Request $request, KeyColor $keyColor): JsonResponse
    {
        $validated = $request->validate([
            'rgb_code' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'name' => ['required', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
        ]);

        $keyColor->update([
            'rgb_code' => strtoupper($validated['rgb_code']),
            'sort_order' => $validated['sort_order'] ?? $keyColor->sort_order,
            'is_active' => $validated['is_active'] ?? $keyColor->is_active,
        ]);

        $this->syncTranslations($keyColor, $validated['name'], $request->input('translations'));
        $keyColor->load('translations');

        return response()->json(['success' => true, 'data' => $this->serialize($keyColor, true)]);
    }

    public function toggle(KeyColor $keyColor): JsonResponse
    {
        $keyColor->update(['is_active' => ! $keyColor->is_active]);
        $keyColor->load('translations');

        return response()->json(['success' => true, 'data' => $this->serialize($keyColor)]);
    }

    /** @return array<string, mixed> */
    private function serialize(KeyColor $keyColor, bool $withTranslations = false): array
    {
        $data = [
            'id' => $keyColor->id,
            'rgb_code' => $keyColor->rgb_code,
            'name' => $keyColor->name,
            'sort_order' => (int) $keyColor->sort_order,
            'is_active' => (bool) $keyColor->is_active,
        ];
        if ($withTranslations) {
            $data['translations'] = $keyColor->translations->keyBy('locale')->map(fn ($t) => ['name' => $t->name])->all();
        }

        return $data;
    }

    private function syncTranslations(KeyColor $keyColor, string $primaryName, mixed $translations): void
    {
        $by = ['ca' => ['name' => $primaryName]];
        if (is_array($translations)) {
            foreach ($translations as $loc => $payload) {
                if (in_array((string) $loc, CatalogLocale::SUPPORTED, true) && is_array($payload)) {
                    $by[(string) $loc] = array_merge($by[(string) $loc] ?? [], $payload);
                }
            }
        }
        CatalogTranslationSync::syncKeyColorTranslations($keyColor, $by);
    }
}
