<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Public-facing review (published only). Client shown as initials only for privacy. */
class ProductReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $initials = null;
        if ($this->relationLoaded('client') && $this->client) {
            $contact = $this->client->relationLoaded('contacts')
                ? $this->client->contacts->firstWhere('is_primary', true) ?? $this->client->contacts->first()
                : null;
            if ($contact) {
                $first = mb_substr((string) ($contact->name ?? ''), 0, 1, 'UTF-8');
                $last = $contact->surname ? mb_substr((string) $contact->surname, 0, 1, 'UTF-8') : '';
                $initials = mb_strtoupper($first.$last, 'UTF-8');
            }
            // Fallback: first letter of login email
            if (! $initials && $this->client->login_email) {
                $initials = mb_strtoupper(mb_substr((string) $this->client->login_email, 0, 1, 'UTF-8'), 'UTF-8');
            }
        }

        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'verified_purchase' => $this->order_id !== null,
            'client_initials' => $initials,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
