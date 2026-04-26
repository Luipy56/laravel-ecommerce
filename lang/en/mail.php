<?php

return [
    'layout' => [
        'footer_default' => 'This is an automated message; please do not reply directly.',
        'manage_preferences' => 'Manage your data using the secure link',
        'fallback_url_intro' => 'If the button does not work, paste this address into the browser:',
    ],

    'installation_price' => [
        'subject' => 'Installation quote for order #:id',
        'greeting' => 'Hello,',
        'body' => 'We have set the installation price for your order #:id. The breakdown is below.',
        'products_subtotal' => 'Products subtotal',
        'installation_fee' => 'Installation',
        'total_due' => 'Total due',
        'cta' => 'You can review the order and complete payment in the store.',
        'button' => 'View order',
        'footer' => 'Thank you for your trust.',
    ],

    'order_payment_confirmed' => [
        'subject' => 'Payment confirmed · order #:id',
        'greeting' => 'Hello,',
        'body' => 'We have successfully recorded the payment for your order #:id.',
        'total_paid' => 'Total paid',
        'cta' => 'You can check your order details at any time.',
        'button' => 'View order',
        'footer' => 'Thank you for your purchase.',
    ],

    'order_installation_quote' => [
        'subject' => 'We received your installation request · order #:id',
        'greeting' => 'Hello,',
        'body' => 'We have received your order #:id with an installation request.',
        'next_steps' => 'We will email you when we have the installation quote. Meanwhile you can track the status in the store.',
        'button' => 'View order',
        'footer' => 'Thank you for your trust.',
    ],

    'personalized_solution' => [
        'subject' => 'We received your request #:id',
        'greeting' => 'Hello,',
        'body' => 'We have received your custom solution request (reference #:id). Our team will review it and contact you if more information is needed.',
        'footer' => 'Thank you for contacting us.',
        'reference_code_label' => 'Access code (64 characters) to follow up without signing in to the store:',
        'portal_intro' => 'You can track the request and update your details with this secure link (no sign-in required):',
        'portal_button' => 'Open request manager',
    ],

    'personalized_solution_resolved' => [
        'subject' => 'Custom solution · update #:id',
        'greeting' => 'Hello,',
        'body' => 'There is an update on your request #:id.',
        'reference_code_label' => 'Access code (64 characters) to follow up without signing in to the store:',
        'cta' => 'You can review the details and reply from the manager.',
        'button' => 'View request',
    ],

    'admin_personalized_improvement' => [
        'subject' => '[Solution #:id] Improvement requested by the customer',
        'intro' => 'The customer requested changes for request #:id.',
        'email' => 'Email:',
        'message' => 'Message:',
        'button' => 'Open in admin',
    ],

    'order_shipped' => [
        'subject' => 'Your order #:id is on the way',
        'greeting' => 'Hello,',
        'body' => 'Your order #:id has been marked as shipped.',
        'shipping_date_label' => 'Planned ship date',
        'cta' => 'You can review the details and status in the store.',
        'button' => 'View order',
        'footer' => 'Thank you for your trust.',
    ],
];
