<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $active = $request->query('is_active');

        $query = Faq::query()->orderBy('sort_order')->orderBy('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('question_ca', 'like', '%'.$search.'%')
                    ->orWhere('question_es', 'like', '%'.$search.'%')
                    ->orWhere('question_en', 'like', '%'.$search.'%')
                    ->orWhere('answer_ca', 'like', '%'.$search.'%')
                    ->orWhere('answer_es', 'like', '%'.$search.'%')
                    ->orWhere('answer_en', 'like', '%'.$search.'%');
            });
        }

        if ($active === '1' || $active === '0') {
            $query->where('is_active', $active === '1');
        }

        $faqs = $query->paginate(min(max((int) $request->query('per_page', 25), 5), 100));

        return response()->json([
            'success' => true,
            'data' => $faqs->items(),
            'meta' => [
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
                'per_page' => $faqs->perPage(),
                'total' => $faqs->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'question_ca' => ['required', 'string'],
            'question_es' => ['required', 'string'],
            'question_en' => ['required', 'string'],
            'answer_ca' => ['required', 'string'],
            'answer_es' => ['required', 'string'],
            'answer_en' => ['required', 'string'],
        ]);

        $faq = Faq::query()->create([
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'question_ca' => $validated['question_ca'],
            'question_es' => $validated['question_es'],
            'question_en' => $validated['question_en'],
            'answer_ca' => $validated['answer_ca'],
            'answer_es' => $validated['answer_es'],
            'answer_en' => $validated['answer_en'],
        ]);

        return response()->json(['success' => true, 'data' => $faq], 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $faq]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'question_ca' => ['sometimes', 'string'],
            'question_es' => ['sometimes', 'string'],
            'question_en' => ['sometimes', 'string'],
            'answer_ca' => ['sometimes', 'string'],
            'answer_es' => ['sometimes', 'string'],
            'answer_en' => ['sometimes', 'string'],
        ]);

        $faq->update($validated);

        return response()->json(['success' => true, 'data' => $faq->fresh()]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json(['success' => true]);
    }
}
