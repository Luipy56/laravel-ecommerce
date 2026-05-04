<?php

return [
    'attributes' => [
        'login_email' => 'email address',
        'email' => 'email address',
    ],

    'custom' => [
        'login_email' => [
            'email' => 'The email address is invalid or the domain cannot receive mail.',
        ],
        'email' => [
            'email' => 'The email address is invalid or the domain cannot receive mail.',
        ],
        'address_postal_code' => [
            'regex' => 'Postal code must contain digits only (up to 20).',
        ],
        'shipping_postal_code' => [
            'regex' => 'Postal code must contain digits only (up to 20).',
        ],
        'installation_postal_code' => [
            'regex' => 'Postal code must contain digits only (up to 20).',
        ],
        'postal_code' => [
            'regex' => 'Postal code must contain digits only (up to 20).',
        ],
        'installation_auto_pricing' => [
            'tiers_required' => 'Add at least one installation tier with a positive merchandise ceiling.',
            'last_tier_max_must_match_quote' => 'The highest tier ceiling (EUR) must equal the quote threshold (EUR) so automatic pricing covers all merchandise up to that limit.',
            'tiers_must_increase' => 'Each tier ceiling must be greater than the previous one.',
        ],
    ],
];
