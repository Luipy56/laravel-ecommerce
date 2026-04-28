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
    ],
];
