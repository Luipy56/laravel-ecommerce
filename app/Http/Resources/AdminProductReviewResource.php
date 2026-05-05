<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Full review representation for admin moderation. */
class AdminProductReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $clientName = null;
        $clientEmail = null;
        if ($this->relationLoaded('client') && $this->client) {
            $clientEmail = $this->client->login_email;
            $contact = $this->client->relationLoaded('contacts')
                ? $this->client->contacts->firstWhere('is_primary', true) ?? $this->client->contacts->first()
                : null;
            if ($contact) {
                $clientName = trim(($contact->name ?? '').' '.($contact->surname ?? ''));
            }
        }

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'client_id' => $this->client_id,
            'order_id' => $this->order_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'admin_note' => $this->admin_note,
            'verified_purchase' => $this->order_id !== null,
            'client_name' => $clientName,
            'client_email' => $clientEmail,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'code' => $this->product->code,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
