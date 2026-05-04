<?php

return [
    'attributes' => [
        'login_email' => 'correu electrònic',
        'email' => 'correu electrònic',
    ],

    'custom' => [
        'login_email' => [
            'email' => 'El correu electrònic no és vàlid o el domini no pot rebre correu.',
        ],
        'email' => [
            'email' => 'El correu electrònic no és vàlid o el domini no pot rebre correu.',
        ],
        'address_postal_code' => [
            'regex' => 'El codi postal ha de tenir només xifres (fins a 20).',
        ],
        'shipping_postal_code' => [
            'regex' => 'El codi postal ha de tenir només xifres (fins a 20).',
        ],
        'installation_postal_code' => [
            'regex' => 'El codi postal ha de tenir només xifres (fins a 20).',
        ],
        'postal_code' => [
            'regex' => 'El codi postal ha de tenir només xifres (fins a 20).',
        ],
        'installation_auto_pricing' => [
            'tiers_required' => 'Afegeix com a mínim un tram amb un sostre de mercaderia positiu.',
            'last_tier_max_must_match_quote' => 'El sostre del darrer tram (EUR) ha de coincidir amb el llindar de consulta (EUR) per cobrir tota la mercaderia fins aquest límit.',
            'tiers_must_increase' => 'Cada sostre de tram ha de ser major que l’anterior.',
        ],
    ],
];
