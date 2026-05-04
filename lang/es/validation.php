<?php

return [
    'attributes' => [
        'login_email' => 'correo electrónico',
        'email' => 'correo electrónico',
    ],

    'custom' => [
        'login_email' => [
            'email' => 'El correo electrónico no es válido o el dominio no puede recibir correo.',
        ],
        'email' => [
            'email' => 'El correo electrónico no es válido o el dominio no puede recibir correo.',
        ],
        'address_postal_code' => [
            'regex' => 'El código postal solo puede contener dígitos (hasta 20).',
        ],
        'shipping_postal_code' => [
            'regex' => 'El código postal solo puede contener dígitos (hasta 20).',
        ],
        'installation_postal_code' => [
            'regex' => 'El código postal solo puede contener dígitos (hasta 20).',
        ],
        'postal_code' => [
            'regex' => 'El código postal solo puede contener dígitos (hasta 20).',
        ],
        'installation_auto_pricing' => [
            'tiers_required' => 'Añade al menos un tramo con un tope de mercancía positivo.',
            'last_tier_max_must_match_quote' => 'El tope del último tramo (EUR) debe coincidir con el umbral de consulta (EUR) para cubrir toda la mercancía hasta ese límite.',
            'tiers_must_increase' => 'Cada tope de tramo debe ser mayor que el anterior.',
        ],
    ],
];
