<?php

namespace App\Events;

use App\Models\PersonalizedSolution;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonalizedSolutionSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PersonalizedSolution $solution,
        public string $locale,
    ) {}
}
