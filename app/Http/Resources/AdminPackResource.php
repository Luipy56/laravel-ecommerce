<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Pack representation for admin (includes product_ids for form). */
class AdminPackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'is_trending' => (bool) $this->is_trending,
            'is_active' => (bool) $this->is_active,
            'contains_keys' => (bool) $this->contains_keys,
            'product_ids' => $this->whenLoaded('packItems', fn () => $this->packItems->pluck('product_id')->values()->all()),
            'items' => $this->whenLoaded('packItems', fn () => $this->packItems
                ->filter(fn ($i) => $i->product)
                ->map(fn ($i) => [
                    'product_id' => $i->product_id,
                    'product' => [
                        'id' => $i->product->id,
                        'name' => $i->product->name,
                        'code' => $i->product->code,
                        'price' => $i->product->price !== null ? (float) $i->product->price : null,
                        'stock' => (int) $i->product->stock,
                        'is_active' => (bool) $i->product->is_active,
                        'category' => $i->product->relationLoaded('category') && $i->product->category
                            ? ['id' => $i->product->category->id, 'name' => $i->product->category->name]
                            : null,
                        'image_url' => $i->product->relationLoaded('images') && $i->product->images->isNotEmpty()
                            ? $i->product->images->first()->url
                            : null,
                    ],
                ])
                ->values()
                ->all()),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ])),
        ];
    }
}
