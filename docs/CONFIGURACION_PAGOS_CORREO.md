# Configuración de pagos, correo y entorno

Guía para administradores y despliegue: variables de entorno necesarias para que el checkout y el pago desde la ficha de pedido funcionen, y para que se envíen correos (por ejemplo, cuando se indica el precio de instalación).

## Pagos: qué hace falta y cómo se ve en la tienda

La aplicación no muestra formularios “del banco” genéricos en vacío: cada método depende de un proveedor configurado.

| Método en la tienda | Proveedor real | Comportamiento cuando está bien configurado |
|---------------------|----------------|---------------------------------------------|
| Tarjeta, PayPal     | **Stripe** (`STRIPE_KEY` + `STRIPE_SECRET`) | Tras iniciar el pago, aparece el bloque de Stripe (elemento embebido) en la misma página para introducir la tarjeta. PayPal en este proyecto se canaliza por Stripe. |
| Bizum               | **Redsys** (`REDSYS_MERCHANT_CODE` + `REDSYS_SECRET_KEY`, más terminal y entorno) | Redirección al TPV / pasarela del banco (formulario automático). |
| Revolut             | **Revolut Merchant** (`REVOLUT_MERCHANT_API_KEY`) | Redirección a la URL de pago de Revolut. |

Si faltan credenciales, el listado de métodos en checkout y en el pedido se acorta o queda vacío, y las peticiones `POST …/orders/.../pay` pueden responder **422** con código `payment_method_not_configured`. Los mensajes tipo “Stripe is not configured” indican exactamente eso: falta configuración en `.env`, no un fallo del navegador.

### Variables (resumen)

Copia desde `.env.example` y rellena según el proveedor que uses:

- **Stripe:** `STRIPE_KEY`, `STRIPE_SECRET`, y en producción `STRIPE_WEBHOOK_SECRET` para confirmar pagos de forma fiable vía webhook.
- **Redsys:** `REDSYS_MERCHANT_CODE`, `REDSYS_SECRET_KEY`, `REDSYS_TERMINAL` (por defecto `001`), `REDSYS_ENVIRONMENT` (`test` o producción según contrato).
- **Revolut:** `REVOLUT_MERCHANT_API_KEY`, `REVOLUT_SANDBOX`, `REVOLUT_API_VERSION`, y opcionalmente `REVOLUT_WEBHOOK_SECRET`.

Tras cambiar `.env`, reinicia PHP-FPM / el contenedor o `php artisan config:clear` según tu despliegue.

### Desarrollo local sin PSP real

Solo para entornos de prueba, con **`APP_DEBUG=true`**, puedes activar:

```env
PAYMENTS_ALLOW_SIMULATED=true
```

Así el servidor considera los métodos “disponibles” y puede marcar pagos como simulados sin llamar a Stripe/Redsys/Revolut. **No uses esto en producción** (`APP_DEBUG=false` y `PAYMENTS_ALLOW_SIMULATED=false`).

El endpoint público `GET /api/v1/payments/config` expone qué métodos el servidor puede iniciar (útil para depurar).

## Correo electrónico (precio de instalación y demás)

Cuando un administrador guarda un **precio de instalación** y el pedido pasa a estado listo para que el cliente pague, se dispara el envío del correo `InstallationPriceAssignedMail`.

1. Configura el transporte en `.env`, por ejemplo:
   - `MAIL_MAILER=smtp` (o `log` solo para ver el contenido en logs en local).
   - `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` si aplica.
   - `MAIL_FROM_ADDRESS` y `MAIL_FROM_NAME` (deben ser válidos para tu proveedor).

2. El listener envía el correo **de forma síncrona** (no hace falta `queue:work` para ese correo concreto). Si más adelante se vuelve a encolar, necesitarías un worker con `php artisan queue:work` y `QUEUE_CONNECTION` adecuado.

3. El cliente debe tener **email de login** (`login_email`); si falta, el envío se omite y quedará constancia en el log.

## Sobre `POST /api/v1/login` con 422

Un **422** en login suele ser **validación** (campos incorrectos o faltantes) o credenciales rechazadas según cómo esté implementado el controlador. No está relacionado con Stripe o Redsys. Revisa el cuerpo JSON de la respuesta (`message` o `errors`) en las herramientas de red del navegador.

## Checklist rápido antes de producción

- [ ] `STRIPE_*` o `REDSYS_*` o `REVOLUT_MERCHANT_API_KEY` configurados según los métodos que quieras ofrecer.
- [ ] Webhooks de Stripe/Revolut apuntando a URLs públicas HTTPS y secretos en `.env`.
- [ ] `APP_DEBUG=false`, `PAYMENTS_ALLOW_SIMULATED=false`.
- [ ] `MAIL_*` apuntando a un SMTP o servicio real y `MAIL_FROM_*` correctos.
