<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Product representation for admin (includes category_id and relations for forms). */
class AdminProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'variant_group_id' => $this->variant_group_id,
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
            'is_trending' => (bool) $this->is_trending,
            'is_active' => (bool) $this->is_active,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'code' => $this->category->code,
                'name' => $this->category->name,
            ]),
            'features' => $this->whenLoaded('features', fn () => $this->features->map(fn ($f) => [
                'id' => $f->id,
                'feature_name_id' => $f->feature_name_id,
                'value' => $f->value,
                'type' => $f->featureName?->name,
            ])),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ])),
        ];
    }
}
