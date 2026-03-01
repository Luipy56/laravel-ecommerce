<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock' => (int) $this->stock,
            'is_installable' => (bool) $this->is_installable,
            'installation_price' => $this->installation_price ? (float) $this->installation_price : null,
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
                'value' => $f->value,
            ])),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ])),
            'variant_options' => $this->whenLoaded('variantGroup', function () {
                $products = $this->variantGroup?->products ?? collect();
                return $products->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                ])->values()->all();
            }),
        ];
    }
}
