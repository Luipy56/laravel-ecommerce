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
    ],
];
