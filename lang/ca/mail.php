<?php

return [
    'layout' => [
        'footer_default' => 'Missatge automàtic; si us plau no responguis directament al correu.',
        'manage_preferences' => 'Gestiona les teves dades amb l’enllaç segur',
    ],

    'installation_price' => [
        'subject' => 'Presupost d’instal·lació per a la comanda #:id',
        'greeting' => 'Hola,',
        'body' => 'Ja hem definit el preu d’instal·lació per a la teva comanda #:id. A continuació tens el desglossament.',
        'products_subtotal' => 'Subtotal productes',
        'installation_fee' => 'Instal·lació',
        'total_due' => 'Total a pagar',
        'cta' => 'Pots revisar la comanda i completar el pagament des de la botiga.',
        'button' => 'Anar a la comanda',
        'footer' => 'Gràcies per confiar en nosaltres.',
    ],

    'order_payment_confirmed' => [
        'subject' => 'Pagament confirmat · comanda #:id',
        'greeting' => 'Hola,',
        'body' => 'Hem registrat correctament el pagament de la teva comanda #:id.',
        'total_paid' => 'Total pagat',
        'cta' => 'Pots consultar el detall de la comanda quan vulguis.',
        'button' => 'Veure la comanda',
        'footer' => 'Gràcies per la teva compra.',
    ],

    'order_payment_pending' => [
        'subject' => 'Comanda #:id registrada · pendent de pagament',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut la teva comanda #:id. La comanda queda pendent: encara no hem rebut el pagament.',
        'total_due' => 'Total a pagar',
        'cta' => 'Completa el pagament des de la botiga amb l’enllaç següent (necessari si s’ha obert PayPal, Stripe o un altre pagament a part).',
        'button' => 'Anar a la comanda',
        'footer' => 'Si ja has completat el pagament, en breu rebràs la confirmació quan el banc o la passarel·la l’hagin registrat.',
    ],

    'admin_order_installation_quote' => [
        'subject' => '[Comanda #:id] Sol·licitud de pressupost d’instal·lació',
        'intro' => 'S’ha creat una comanda amb instal·lació al damunt del màxim autòmat (cal pressupost). Comanda #:id.',
        'client_email' => 'Client:',
        'grand_total' => 'Total (amb enviament)',
        'button' => 'Obrir al panell',
    ],

    'admin_order_payment_pending' => [
        'subject' => '[Comanda #:id] Pendent de pagament',
        'intro' => 'S’ha registrat una comanda sense confirmació de pagament (redirecció o pendent a la passarel·la). Comanda #:id.',
        'client_email' => 'Client:',
        'grand_total' => 'Total',
        'order_status' => 'Estat',
        'button' => 'Obrir al panell',
    ],

    'order_installation_quote' => [
        'subject' => 'Hem rebut la teva sol·licitud d’instal·lació · comanda #:id',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut la teva comanda #:id amb sol·licitud d’instal·lació.',
        'next_steps' => 'Et enviarem un correu quan tinguem el pressupost d’instal·lació. Fins llavors pots seguir l’estat des de la botiga.',
        'button' => 'Veure la comanda',
        'footer' => 'Gràcies per confiar en nosaltres.',
    ],

    'personalized_solution' => [
        'subject' => 'Sol·licitud personalitzada rebuta · n. :id',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut la teva sol·licitud de solució personalitzada (referència #:id). El nostre equip la revisarà i es posarà en contacte amb tu si cal més informació.',
        'footer' => 'Gràcies per contactar-nos.',
        'portal_intro' => 'Pots fer el seguiment de la sol·licitud i actualitzar les teves dades amb aquest enllaç segur (sense iniciar sessió):',
        'portal_button' => 'Obrir el gestor de la sol·licitud',
    ],

    'personalized_solution_resolved' => [
        'subject' => 'Solució personalitzada · notícies sobre el teu tràmit #:id',
        'greeting' => 'Hola,',
        'body' => 'Hi ha una novetat sobre la teva sol·licitud #:id.',
        'cta' => 'Pots revisar els detalls i respondre des del gestor.',
        'button' => 'Veure la sol·licitud',
    ],

    'admin_personalized_improvement' => [
        'subject' => '[Solució #:id] Millora sol·licitada pel client',
        'intro' => 'El client ha sol·licitat millores per a la sol·licitud #:id.',
        'email' => 'Correu:',
        'message' => 'Missatge:',
        'button' => 'Obrir al panell',
    ],

    'order_shipped' => [
        'subject' => 'La teva comanda #:id està en camí',
        'greeting' => 'Hola,',
        'body' => 'La teva comanda #:id ha passat a estat d’enviament.',
        'shipping_date_label' => 'Data d’enviament prevista',
        'cta' => 'Pots revisar el detall i l’estat des de la botiga.',
        'button' => 'Veure la comanda',
        'footer' => 'Gràcies per confiar en nosaltres.',
    ],
];
