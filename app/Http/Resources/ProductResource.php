<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $listPrice = (float) $this->price;
        $discount = $this->discount_percent;
        $hasDiscount = $discount !== null && (float) $discount > 0;
        $effective = $this->effectivePrice();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $effective,
            'list_price' => $hasDiscount ? $listPrice : null,
            'discount_percent' => $hasDiscount ? (float) $discount : null,
            'stock' => (int) $this->stock,
            'weight_kg' => $this->weight_kg !== null ? (float) $this->weight_kg : null,
            'is_double_clutch' => (bool) $this->is_double_clutch,
            'has_card' => (bool) $this->has_card,
            'security_level' => $this->security_level,
            'competitor_url' => $this->competitor_url,
            'is_extra_keys_available' => (bool) $this->is_extra_keys_available,
            'extra_key_unit_price' => $this->extra_key_unit_price ? (float) $this->extra_key_unit_price : null,
            'is_featured' => (bool) $this->is_featured,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'code' => $this->category->code,
                'name' => $this->category->name,
            ]),
            'features' => $this->whenLoaded('features', fn () => $this->features->map(fn ($f) => [
                'id' => $f->id,
                'type' => $f->featureName?->name,
                'feature_name_code' => $f->featureName?->code,
                'value' => $f->value,
            ])),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ])),
            'variant_options' => $this->whenLoaded('variantGroup', function () {
                $products = $this->variantGroup?->products ?? collect();

                return $products->map(function ($p) {
                    $labelFeature = $p->features->first(fn ($f) => ($f->featureName?->code ?? '') === 'inner_measure')
                        ?? $p->features->first(fn ($f) => ($f->featureName?->code ?? '') === 'outer_measure')
                        ?? $p->features->first();
                    $fn = $labelFeature?->featureName;
                    $typeLabel = $fn?->name ?? '';
                    $val = $labelFeature?->value ?? '';
                    $variantLabel = $labelFeature
                        ? trim(($typeLabel !== '' ? $typeLabel.': ' : '').(is_string($val) ? $val : ''))
                        : ($p->name ?: $p->code ?? '');
                    $price = $p->effectivePrice();

                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'code' => $p->code,
                        'price' => $price,
                        'formatted_price' => number_format($price, 2, ',', '.').' €',
                        'image_url' => $p->images->first()?->url,
                        'variant_label' => $variantLabel,
                    ];
                })->values()->all();
            }),
        ];
    }
}
