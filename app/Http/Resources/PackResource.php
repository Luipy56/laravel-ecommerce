<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $listPrice = (float) $this->price;
        $discount = $this->discount_percent;
        $hasDiscount = $discount !== null && (float) $discount > 0;
        $effective = $this->effectivePrice();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $effective,
            'list_price' => $hasDiscount ? $listPrice : null,
            'discount_percent' => $hasDiscount ? (float) $discount : null,
            'contains_keys' => (bool) $this->contains_keys,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'product' => $i->product ? [
                    'id' => $i->product->id,
                    'name' => $i->product->name,
                    'code' => $i->product->code,
                    'price' => (float) $i->product->price,
                    'image_url' => $i->product->relationLoaded('images')
                        ? ($i->product->images->first()?->url ?? null)
                        : null,
                ] : null,
            ])),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
                'content_type' => $img->content_type,
            ])),
        ];
    }
}
