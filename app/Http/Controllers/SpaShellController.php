<?php

namespace App\Http\Controllers;

use App\Services\Seo\ShareMetaService;
use App\Support\RequestLocale;
use App\Support\ShareMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Serves the React SPA shell with route-specific Open Graph / Twitter meta in the initial HTML.
 */
class SpaShellController extends Controller
{
    public function __construct(
        private readonly ShareMetaService $shareMeta,
    ) {}

    public function home(Request $request): View
    {
        return $this->render($request, fn (string $locale) => $this->shareMeta->forHome($locale));
    }

    public function products(Request $request): View
    {
        return $this->render($request, fn (string $locale) => $this->shareMeta->forProducts($locale));
    }

    public function product(Request $request, int $id): View
    {
        return $this->render($request, fn (string $locale) => $this->shareMeta->forProduct($id, $locale));
    }

    public function pack(Request $request, int $id): View
    {
        return $this->render($request, fn (string $locale) => $this->shareMeta->forPack($id, $locale));
    }

    public function categoryProducts(Request $request, int $id): View
    {
        return $this->render($request, fn (string $locale) => $this->shareMeta->forCategory($id, $locale));
    }

    public function fallback(Request $request): View
    {
        return $this->render($request, fn (string $locale) => $this->shareMeta->forFallback($locale));
    }

    /**
     * @param  callable(string): ShareMeta  $builder
     */
    private function render(Request $request, callable $builder): View
    {
        $locale = RequestLocale::fromRequest($request);
        app()->setLocale($locale);

        return view('welcome', [
            'shareMeta' => $builder($locale),
        ]);
    }
}
