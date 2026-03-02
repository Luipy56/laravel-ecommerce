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
            'product_ids' => $this->whenLoaded('packItems', fn () => $this->packItems->pluck('product_id')->values()->all()),
            'items' => $this->whenLoaded('packItems', fn () => $this->packItems->map(fn ($i) => [
                'product_id' => $i->product_id,
                'product' => $i->product ? [
                    'id' => $i->product->id,
                    'name' => $i->product->name,
                    'code' => $i->product->code,
                ] : null,
            ])),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ])),
        ];
    }
}
