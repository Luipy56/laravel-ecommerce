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
    ],
];
