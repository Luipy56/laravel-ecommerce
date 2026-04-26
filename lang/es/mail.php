<?php

return [
    'layout' => [
        'footer_default' => 'Mensaje automático; por favor no respondas directamente.',
        'manage_preferences' => 'Gestiona tus datos con el enlace seguro',
        'fallback_url_intro' => 'Si el botón no funciona, pega esta dirección en el navegador:',
    ],

    'installation_price' => [
        'subject' => 'Presupuesto de instalación para el pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos definido el precio de instalación para tu pedido #:id. A continuación tienes el desglose.',
        'products_subtotal' => 'Subtotal productos',
        'installation_fee' => 'Instalación',
        'total_due' => 'Total a pagar',
        'cta' => 'Puedes revisar el pedido y completar el pago desde la tienda.',
        'button' => 'Ir al pedido',
        'footer' => 'Gracias por confiar en nosotros.',
    ],

    'order_payment_confirmed' => [
        'subject' => 'Pago confirmado · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos registrado correctamente el pago de tu pedido #:id.',
        'total_paid' => 'Total pagado',
        'cta' => 'Puedes consultar el detalle del pedido cuando quieras.',
        'button' => 'Ver el pedido',
        'footer' => 'Gracias por tu compra.',
    ],

    'order_installation_quote' => [
        'subject' => 'Hemos recibido tu solicitud de instalación · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos recibido tu pedido #:id con solicitud de instalación.',
        'next_steps' => 'Te enviaremos un correo cuando tengamos el presupuesto de instalación. Mientras tanto puedes seguir el estado desde la tienda.',
        'button' => 'Ver el pedido',
        'footer' => 'Gracias por confiar en nosotros.',
    ],

    'personalized_solution' => [
        'subject' => 'Hemos recibido tu solicitud #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos recibido tu solicitud de solución personalizada (referencia #:id). Nuestro equipo la revisará y se pondrá en contacto contigo si necesita más información.',
        'footer' => 'Gracias por contactarnos.',
        'reference_code_label' => 'Código de acceso (64 caracteres) para seguimiento sin la tienda:',
        'portal_intro' => 'Puedes hacer seguimiento de la solicitud y actualizar tus datos con este enlace seguro (sin iniciar sesión):',
        'portal_button' => 'Abrir el gestor de la solicitud',
    ],

    'personalized_solution_resolved' => [
        'subject' => 'Solución personalizada · actualización #:id',
        'greeting' => 'Hola,',
        'body' => 'Hay una novedad sobre tu solicitud #:id.',
        'reference_code_label' => 'Código de acceso (64 caracteres) para seguimiento sin la tienda:',
        'cta' => 'Puedes revisar los detalles y responder desde el gestor.',
        'button' => 'Ver la solicitud',
    ],

    'admin_personalized_improvement' => [
        'subject' => '[Solución #:id] Mejora solicitada por el cliente',
        'intro' => 'El cliente ha solicitado mejoras para la solicitud #:id.',
        'email' => 'Correo:',
        'message' => 'Mensaje:',
        'button' => 'Abrir en el panel',
    ],

    'order_shipped' => [
        'subject' => 'Tu pedido #:id va en camino',
        'greeting' => 'Hola,',
        'body' => 'Tu pedido #:id ha pasado a estado de envío.',
        'shipping_date_label' => 'Fecha de envío prevista',
        'cta' => 'Puedes revisar el detalle y el estado desde la tienda.',
        'button' => 'Ver el pedido',
        'footer' => 'Gracias por confiar en nosotros.',
    ],
];
