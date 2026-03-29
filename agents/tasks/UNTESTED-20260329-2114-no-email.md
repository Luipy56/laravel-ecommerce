# NO email

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/6

## Problem / goal
Los clientes finales no reciben correos al comprar, al enviar una solicitud de solución personalizada, etc. Se necesita confirmación por email y la información pertinente en esos flujos.

## High-level instructions for coder
- Auditar qué eventos de negocio deben disparar correo (pedido confirmado, envío, solicitud personalizada, etc.) frente a lo que ya existe en código y configuración.
- Revisar colas, notificaciones Laravel (`Mail`, `Notification`) y variables de entorno (`MAIL_*`, colas) para asegurar envío fiable en local y producción.
- Definir plantillas o clases de correo con textos traducibles (ca/es) según reglas del proyecto.
- Documentar en README o `docs/` lo mínimo para que operaciones pueda verificar el envío (sin exponer secretos).

## Implementation notes (coder)
- **Pago confirmado:** `PaymentCompletionService::markSucceeded()` dispara `OrderPaymentSucceeded` la primera vez que el pago pasa a `succeeded` (webhooks Stripe/Redsys/Revolut, captura PayPal, y checkout simulado vía `PaymentCheckoutService::simulateSuccess()` unificado con el mismo servicio).
- **Comanda amb pressupost d’instal·lació:** després del checkout amb `installation_requested`, `OrderInstallationQuoteRequested`.
- **Solució personalitzada:** després de `POST /api/v1/personalized-solutions`, `PersonalizedSolutionSubmitted` (idioma per `Accept-Language` ca/es).
- **Enviament:** quan l’admin passa la comanda a `in_transit` o `sent` des d’un estat que no era ja d’enviament, `OrderShipped`.
- **Presupuesto instalación (existente):** sense canvis de comportament, només `MailLocale` compartit.
- Textos: `lang/ca/mail.php`, `lang/es/mail.php`; vistes HTML a `resources/views/emails/`.
- Operacions: `docs/email-notifications.md`.

## Testing instructions
1. Des del directori arrel del repositori: `php artisan test` (inclou `Tests\Feature\CustomerTransactionalEmailTest`).
2. `php artisan routes:smoke` (sense respostes HTTP 500).
3. Amb `MAIL_MAILER=log` al `.env`, fer un checkout amb pagament simulat (sense claus PSP reals) i comprovar al log que s’intenten els correus de confirmació de pagament i, si aplica, sol·licitud d’instal·lació.
4. Enviar el formulari de solució personalitzada (públic) i comprovar el correu de recepció a l’adreça indicada.
5. Com a admin, canviar una comanda `pending` a `in_transit` i comprovar el correu d’enviament al client.
6. Opcional: amb SMTP real de prova, repetir 3–5 i verificar la bústia.
