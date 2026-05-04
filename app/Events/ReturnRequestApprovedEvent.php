<?php

namespace App\Events;

use App\Models\ReturnRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReturnRequestApprovedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ReturnRequest $returnRequest) {}
}
