<?php

return [
    'layout' => [
        'footer_default' => 'Missatge automàtic; si us plau no respongui directament al correu.',
        'manage_preferences' => 'Gestioni les seves dades amb l'enllaç segur',
    ],

    'installation_price' => [
        'subject' => 'Presupost d'instal·lació per a la comanda #:id',
        'greeting' => 'Hola,',
        'body' => 'Ja hem definit el preu d'instal·lació per a la seva comanda #:id. A continuació té el desglossament.',
        'products_subtotal' => 'Subtotal productes',
        'installation_fee' => 'Instal·lació',
        'total_due' => 'Total a pagar',
        'cta' => 'Pot revisar la comanda i completar el pagament des de la botiga.',
        'button' => 'Anar a la comanda',
        'footer' => 'Gràcies per confiar en nosaltres.',
    ],

    'order_payment_confirmed' => [
        'subject' => 'Pagament confirmat · comanda #:id',
        'greeting' => 'Hola,',
        'body' => 'Hem registrat correctament el pagament de la seva comanda #:id.',
        'total_paid' => 'Total pagat',
        'cta' => 'Pot consultar el detall de la comanda quan vulgui.',
        'button' => 'Veure la comanda',
        'footer' => 'Gràcies per la seva compra.',
    ],

    'order_payment_pending' => [
        'subject' => 'Comanda #:id registrada · pendent de pagament',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut la seva comanda #:id. La comanda queda pendent: encara no hem rebut el pagament.',
        'total_due' => 'Total a pagar',
        'cta' => 'Completa el pagament des de la botiga amb l'enllaç següent.',
        'button' => 'Anar a la comanda',
        'footer' => 'Si ja ha completat el pagament, en breu rebrà la confirmació quan el banc o la passarel·la l'hagin registrat.',
    ],

    'admin_order_installation_quote' => [
        'subject' => '[Comanda #:id] Sol·licitud de pressupost d'instal·lació',
        'intro' => 'S'ha creat una comanda amb instal·lació al damunt del màxim autòmat (cal pressupost). Comanda #:id.',
        'client_email' => 'Client:',
        'grand_total' => 'Total (amb enviament)',
        'button' => 'Obrir al panell',
    ],

    'admin_order_payment_pending' => [
        'subject' => '[Comanda #:id] Pendent de pagament',
        'intro' => 'S'ha registrat una comanda sense confirmació de pagament (redirecció o pendent a la passarel·la). Comanda #:id.',
        'client_email' => 'Client:',
        'grand_total' => 'Total',
        'order_status' => 'Estat',
        'button' => 'Obrir al panell',
    ],

    'order_installation_quote' => [
        'subject' => 'Hem rebut la seva sol·licitud d'instal·lació · comanda #:id',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut la seva comanda #:id amb sol·licitud d'instal·lació.',
        'next_steps' => 'Li enviarem un correu quan tinguem el pressupost d'instal·lació. Fins llavors pot seguir l'estat des de la botiga.',
        'button' => 'Veure la comanda',
        'footer' => 'Gràcies per confiar en nosaltres.',
    ],

    'personalized_solution' => [
        'subject' => 'Sol·licitud personalitzada rebuda · n. :id',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut la seva sol·licitud de solució personalitzada (referència #:id). El nostre equip la revisarà i es posarà en contacte amb vostè si cal més informació.',
        'footer' => 'Gràcies per contactar-nos.',
        'preview_heading' => 'Resum de la seva sol·licitud',
        'preview_description' => 'Descripció del problema',
        'preview_phone' => 'Telèfon',
        'preview_address' => 'Adreça i notes d'entrega',
        'preview_attachments' => 'Fitxers adjunts',
        'preview_truncated' => 'La descripció és molt llarga; en el gestor de la sol·licitud en veurà el text complet.',
        'portal_intro' => 'Pot fer el seguiment de la sol·licitud i actualitzar les seves dades amb aquest enllaç segur (sense iniciar sessió):',
        'portal_button' => 'Obrir el gestor de la sol·licitud',
    ],

    'personalized_solution_resolved' => [
        'subject' => 'Solució personalitzada · notícies sobre el seu tràmit #:id',
        'greeting' => 'Hola,',
        'body' => 'Hi ha una novetat sobre la seva sol·licitud #:id.',
        'cta' => 'Pot revisar els detalls i respondre des del gestor.',
        'button' => 'Veure la sol·licitud',
    ],

    'admin_personalized_improvement' => [
        'subject' => '[Solució #:id] Millora sol·licitada pel client',
        'intro' => 'El client ha sol·licitat millores per a la sol·licitud #:id.',
        'email' => 'Correu:',
        'message' => 'Missatge:',
        'button' => 'Obrir al panell',
    ],

    'verify_email' => [
        'subject' => 'Verifiqui el seu correu',
        'greeting' => 'Hola,',
        'body' => 'Faci clic al botó següent per confirmar la seva adreça de correu i activar el compte.',
        'button' => 'Verificar correu',
        'link_fallback_label' => 'Si el botó no funciona',
        'line2' => 'Si no ha creat un compte, pot ignorar aquest missatge.',
    ],

    'reset_password' => [
        'subject' => 'Recuperar contrasenya',
        'greeting' => 'Hola,',
        'body' => 'Hem rebut una sol·licitud per restablir la contrasenya del seu compte. Faci clic al botó per triar una contrasenya nova.',
        'button' => 'Restablir contrasenya',
        'expiry' => 'Aquest enllaç caduca en :minutes minuts.',
        'link_fallback_label' => 'Si el botó no funciona',
        'line2' => 'Si no ha sol·licitat canviar la contrasenya, ignori aquest correu.',
    ],

    'order_shipped' => [
        'subject' => 'La seva comanda #:id està en camí',
        'greeting' => 'Hola,',
        'body' => 'La seva comanda #:id ha passat a estat d'enviament.',
        'shipping_date_label' => 'Data d'enviament prevista',
        'cta' => 'Pot revisar el detall i l'estat des de la botiga.',
        'button' => 'Veure la comanda',
        'footer' => 'Gràcies per confiar en nosaltres.',
    ],
];
