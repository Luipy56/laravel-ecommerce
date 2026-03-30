# NO email

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/6

## Problem / goal
Los clientes finales no reciben correos tras comprar, solicitar soluciones a medida u otros flujos similares. Hay que añadir notificaciones por email con confirmación y la información necesaria para cada caso. Ver documentación del repo (`docs/`, `README`) si aplica para despliegue y variables de entorno.

## High-level instructions for coder
- Revisar configuración de correo (driver, cola, remitente, plantillas) y qué eventos del dominio (pedidos, solicitudes, etc.) deberían disparar un email.
- Implementar o conectar el envío transaccional para los escenarios del issue; cadenas visibles al usuario en **ca** y **es** según normas del proyecto.
- Cubrir con pruebas automatizadas lo razonable (por ejemplo driver `array` / log en entorno de test) sin incrustar secretos reales de SMTP.

## Coder implementation notes (UTC 2026-03-30)

- La aplicación **ya** enviaba correos transaccionales de forma síncrona vía listeners en `AppServiceProvider` (`OrderPaymentSucceeded`, `OrderInstallationQuoteRequested`, `InstallationPriceWasAssigned`, `PersonalizedSolutionSubmitted`, `OrderShipped`) y `PaymentCompletionService::markSucceeded()`. Las plantillas y textos **ca/es** están en `lang/*/mail.php` y `resources/views/emails/*`.
- La causa habitual de “no llega el correo” en despliegue es **`MAIL_MAILER=log`** (valor por defecto en `.env.example`): los mensajes solo van al log. Se ampliaron comentarios en **`.env.example`** y la sección **Troubleshooting** en **`docs/email-notifications.md`**.
- Tests: **`CustomerTransactionalEmailTest`** — se añadieron casos para **`POST /api/v1/orders/{order}/pay`** (pago aplazado con Bizum simulado) y para **asignación de precio de instalación** por admin (`InstallationPriceAssignedMail`).

## Testing instructions (tester)

1. `php artisan test` — debe pasar (incluye `CustomerTransactionalEmailTest`).
2. `php artisan routes:smoke` — ningún GET con HTTP 500.
3. Revisión manual opcional en staging: configurar **`MAIL_MAILER=smtp`** (u otro transporte real) y comprobar bandeja del cliente en: checkout con pago simulado o real, pedido con instalación (presupuesto + precio asignado), formulario de solución a medida, cambio de estado a en tránsito en admin.
4. Si en producción siguen sin llegar correos: confirmar que **`MAIL_MAILER` no sea `log`**, remitente verificado y webhooks PSP configurados (ver `docs/email-notifications.md`).
