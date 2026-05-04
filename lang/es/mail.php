<?php

return [
    'layout' => [
        'footer_default' => 'Mensaje automático; por favor no responda directamente.',
        'manage_preferences' => 'Gestione sus datos con el enlace seguro',
    ],

    'installation_price' => [
        'subject' => 'Presupuesto de instalación para el pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos definido el precio de instalación para su pedido #:id. A continuación tiene el desglose.',
        'products_subtotal' => 'Subtotal productos',
        'installation_fee' => 'Instalación',
        'total_due' => 'Total a pagar',
        'cta' => 'Puede revisar el pedido y completar el pago desde la tienda.',
        'button' => 'Ir al pedido',
        'footer' => 'Gracias por confiar en nosotros.',
    ],

    'order_payment_confirmed' => [
        'subject' => 'Pago confirmado · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos registrado correctamente el pago de su pedido #:id.',
        'total_paid' => 'Total pagado',
        'cta' => 'Puede consultar el detalle del pedido cuando quiera.',
        'button' => 'Ver el pedido',
        'footer' => 'Gracias por su compra.',
    ],

    'order_payment_pending' => [
        'subject' => 'Pedido #:id registrado · pago pendiente',
        'greeting' => 'Hola,',
        'body' => 'Hemos recibido su pedido #:id. Sigue pendiente: aún no hemos recibido el pago.',
        'total_due' => 'Total a pagar',
        'cta' => 'Completa el pago desde la tienda con el siguiente enlace.',
        'button' => 'Ir al pedido',
        'footer' => 'Si ya ha pagado, recibirá pronto la confirmación cuando el banco o la pasarela la registre.',
    ],

    'admin_order_payment_confirmed' => [
        'subject'      => '[Pedido #:id] Pago confirmado',
        'intro'        => 'Se ha confirmado el pago del pedido #:id.',
        'client_email' => 'Cliente:',
        'grand_total'  => 'Total pagado',
        'button'       => 'Abrir en el panel',
    ],

    'admin_order_installation_quote' => [
        'subject' => '[Pedido #:id] Solicitud de presupuesto de instalación',
        'intro' => 'Se ha creado un pedido con instalación por encima del tope automático (hace falta presupuesto). Pedido #:id.',
        'client_email' => 'Cliente:',
        'grand_total' => 'Total (con envío)',
        'button' => 'Abrir en el panel',
    ],

    'admin_order_payment_pending' => [
        'subject' => '[Pedido #:id] Pago pendiente',
        'intro' => 'Se ha registrado un pedido sin pago confirmado aún (redirección o pendiente en la pasarela). Pedido #:id.',
        'client_email' => 'Cliente:',
        'grand_total' => 'Total',
        'order_status' => 'Estado',
        'button' => 'Abrir en el panel',
    ],

    'order_installation_quote' => [
        'subject' => 'Hemos recibido su solicitud de instalación · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Hemos recibido su pedido #:id con solicitud de instalación.',
        'next_steps' => 'Le enviaremos un correo cuando tengamos el presupuesto de instalación. Mientras tanto puede seguir el estado desde la tienda.',
        'button' => 'Ver el pedido',
        'footer' => 'Gracias por confiar en nosotros.',
    ],

    'personalized_solution' => [
        'subject' => 'Solicitud personalizada recibida · n.º :id',
        'greeting' => 'Hola,',
        'body' => 'Hemos recibido su solicitud de solución personalizada (referencia #:id). Nuestro equipo la revisará y se pondrá en contacto con usted si necesita más información.',
        'footer' => 'Gracias por contactarnos.',
        'preview_heading' => 'Resumen de su solicitud',
        'preview_description' => 'Descripción del problema',
        'preview_phone' => 'Teléfono',
        'preview_address' => 'Dirección y notas de entrega',
        'preview_attachments' => 'Archivos adjuntos',
        'preview_truncated' => 'La descripción es muy larga; en el gestor de la solicitud podrá leer el texto completo.',
        'portal_intro' => 'Puede hacer seguimiento de la solicitud y actualizar sus datos con este enlace seguro (sin iniciar sesión):',
        'portal_button' => 'Abrir el gestor de la solicitud',
    ],

    'personalized_solution_resolved' => [
        'subject' => 'Solución personalizada · novedad sobre su trámite #:id',
        'greeting' => 'Hola,',
        'body' => 'Hay una novedad sobre su solicitud #:id.',
        'cta' => 'Puede revisar los detalles y responder desde el gestor.',
        'button' => 'Ver la solicitud',
    ],

    'admin_personalized_improvement' => [
        'subject' => '[Solución #:id] Mejora solicitada por el cliente',
        'intro' => 'El cliente ha solicitado mejoras para la solicitud #:id.',
        'email' => 'Correo:',
        'message' => 'Mensaje:',
        'button' => 'Abrir en el panel',
    ],

    'verify_email' => [
        'subject' => 'Verifique su correo',
        'greeting' => 'Hola,',
        'body' => 'Haga clic en el botón siguiente para confirmar su dirección de correo y activar la cuenta.',
        'button' => 'Verificar correo',
        'link_fallback_label' => 'Si el botón no funciona',
        'line2' => 'Si no creó una cuenta, puede ignorar este mensaje.',
    ],

    'reset_password' => [
        'subject' => 'Recuperar contraseña',
        'greeting' => 'Hola,',
        'body' => 'Hemos recibido una solicitud para restablecer la contraseña de su cuenta. Haga clic en el botón para elegir una contraseña nueva.',
        'button' => 'Restablecer contraseña',
        'expiry' => 'Este enlace caduca en :minutes minutos.',
        'link_fallback_label' => 'Si el botón no funciona',
        'line2' => 'Si no solicitó cambiar la contraseña, ignore este correo.',
    ],

    'order_shipped' => [
        'subject' => 'Su pedido #:id va en camino',
        'greeting' => 'Hola,',
        'body' => 'Su pedido #:id ha pasado a estado de envío.',
        'delivery_estimate_today' => 'Previsión de entrega: hoy mismo.',
        'delivery_estimate_few_days' => 'Previsión de entrega: en unos días.',
        'delivery_estimate_soon' => 'Previsión de entrega: le llegará pronto.',
        'shipping_date_label' => 'Fecha de envío prevista',
        'cta' => 'Puede revisar el detalle y el estado desde la tienda.',
        'button' => 'Ver el pedido',
        'footer' => 'Gracias por confiar en nosotros.',
    ],

    'admin_return_request_received' => [
        'subject' => '[Devolución pedido #:id] Nueva solicitud de devolución',
        'intro' => 'Se ha recibido una nueva solicitud de devolución para el pedido #:id.',
        'client_email' => 'Cliente:',
        'reason' => 'Motivo:',
        'button' => 'Gestionar en el panel',
    ],

    'return_request_approved' => [
        'subject' => 'Solicitud de devolución aprobada · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Su solicitud de devolución para el pedido #:id ha sido aprobada.',
        'notes_label' => 'Notas:',
        'cta' => 'El reembolso se procesará en breve.',
        'button' => 'Ver el pedido',
    ],

    'return_request_rejected' => [
        'subject' => 'Solicitud de devolución no aprobada · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'Lamentamos informarle que su solicitud de devolución para el pedido #:id no ha podido ser aprobada.',
        'notes_label' => 'Motivo:',
        'button' => 'Ver el pedido',
    ],

    'return_request_refunded' => [
        'subject' => 'Reembolso procesado · pedido #:id',
        'greeting' => 'Hola,',
        'body' => 'El reembolso de su devolución para el pedido #:id ha sido procesado.',
        'amount_label' => 'Importe reembolsado',
        'cta' => 'El reembolso aparecerá en su cuenta en los próximos días hábiles.',
        'button' => 'Ver el pedido',
    ],
];
