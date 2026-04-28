<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $table = 'stripe_webhook_events';

    protected $fillable = [
        'stripe_event_id',
    ];
}
