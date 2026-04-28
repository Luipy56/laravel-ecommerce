<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('locale');
        $allowed = config('app.available_locales', ['ca', 'es', 'en']);
        if (! is_string($locale) || ! in_array($locale, $allowed, true)) {
            $pref = $request->header('Accept-Language', '');
            $locale = (preg_match('/^(ca|es|en)([-_]|$)/i', $pref, $m) ? strtolower($m[1]) : null);
        }
        if (! is_string($locale) || ! in_array($locale, $allowed, true)) {
            $locale = config('app.locale');
        }

        $qCol = 'question_'.$locale;
        $aCol = 'answer_'.$locale;

        $rows = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([$qCol, $aCol]);

        $data = $rows->map(fn (Faq $f) => [
            'question' => $f->{$qCol} ?? '',
            'answer' => $f->{$aCol} ?? '',
        ])->values()->all();

        return response()->json(['success' => true, 'data' => $data]);
    }
}
