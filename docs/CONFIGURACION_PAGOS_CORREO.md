# Configuración de pagos, correo y entorno

Guía para administradores y despliegue: variables de entorno necesarias para que el checkout y el pago desde la ficha de pedido funcionen, y para que se envíen correos (por ejemplo, cuando se indica el precio de instalación).

## Pagos: qué hace falta y cómo se ve en la tienda

La aplicación no muestra formularios “del banco” genéricos en vacío: cada método depende de un proveedor configurado.

| Método en la tienda | Proveedor real | Comportamiento cuando está bien configurado |
|---------------------|----------------|---------------------------------------------|
| Tarjeta             | **Stripe** (`STRIPE_KEY` + `STRIPE_SECRET`) | Tras iniciar el pago, aparece el bloque de Stripe (elemento embebido) en la misma página para introducir la tarjeta. |
| PayPal              | **PayPal REST** (`PAYPAL_CLIENT_ID` + `PAYPAL_SECRET`, `PAYPAL_MODE=sandbox` o `live`) | Tras iniciar el pago, aparecen los botones de PayPal (SDK); la captura se confirma en el servidor (`POST /api/v1/payments/paypal/capture`). |
| Bizum               | **Redsys** (`REDSYS_MERCHANT_CODE` + `REDSYS_SECRET_KEY`, más terminal y entorno) | Redirección al TPV / pasarela del banco (formulario automático). |
| Revolut             | **Revolut Merchant** (`REVOLUT_MERCHANT_API_KEY`) | Redirección a la URL de pago de Revolut. |

Si faltan credenciales, el listado de métodos en checkout y en el pedido se acorta o queda vacío, y las peticiones `POST …/orders/.../pay` pueden responder **422** con código `payment_method_not_configured`. Los mensajes tipo “Stripe is not configured” o “PayPal is not configured” indican falta de variables en `.env`, no un fallo del navegador. Puedes comprobar OAuth de PayPal con `php artisan paypal:test-credentials` (tras configurar `PAYPAL_*`).

### Variables (resumen)

Copia desde `.env.example` y rellena según el proveedor que uses:

- **Stripe:** `STRIPE_KEY`, `STRIPE_SECRET`, y en producción `STRIPE_WEBHOOK_SECRET` para confirmar pagos de forma fiable vía webhook.
- **PayPal:** `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_MODE` (`sandbox` o `live`).
- **Redsys:** `REDSYS_MERCHANT_CODE`, `REDSYS_SECRET_KEY`, `REDSYS_TERMINAL` (por defecto `001`), `REDSYS_ENVIRONMENT` (`test` o producción según contrato).
- **Revolut:** `REVOLUT_MERCHANT_API_KEY`, `REVOLUT_SANDBOX`, `REVOLUT_API_VERSION`, y opcionalmente `REVOLUT_WEBHOOK_SECRET`.

Tras cambiar `.env`, reinicia PHP-FPM / el contenedor o `php artisan config:clear` según tu despliegue.

### Limitar métodos en la tienda (`PAYMENTS_CHECKOUT_METHODS`)

Por defecto el checkout ofrece **card, paypal, bizum, revolut** (según credenciales y simulación). Para mostrar y aceptar **solo algunos** (por ejemplo solo PayPal), define en `.env`:

```env
PAYMENTS_CHECKOUT_METHODS=paypal
```

Valores válidos: `card`, `paypal`, `bizum`, `revolut` (separados por comas). Los tokens inválidos se ignoran; si la lista queda vacía, se usan los cuatro. Las peticiones con un método fuera de la lista reciben error de validación.

### Desarrollo local sin PSP real

Con **`APP_ENV=local`**, **`APP_DEBUG=true`** y **sin** variable `PAYMENTS_ALLOW_SIMULATED` en `.env`, el proyecto **activa por defecto** el modo que permite **simular** pagos (`config/payments.php`). La simulación se aplica **solo a métodos que no tienen credenciales reales** en `.env` (p. ej. tarjeta sin Stripe). **PayPal no se simula nunca:** hace falta `PAYPAL_CLIENT_ID` y `PAYPAL_SECRET` para que aparezca en el checkout y para abrir el widget del SDK; sin ellos el método PayPal no se ofrece aunque el resto esté en modo desarrollo simulado.

Para forzar que en tu máquina **ningún** pago se complete sin PSP, define:

```env
PAYMENTS_ALLOW_SIMULATED=false
```

También puedes poner `PAYMENTS_ALLOW_SIMULATED=true` aunque no sea necesario en local si usas el valor por defecto anterior.

**No uses simulación en producción** (`APP_DEBUG=false`; en producción `APP_ENV` no debe ser `local`).

El endpoint público `GET /api/v1/payments/config` expone qué métodos el storefront puede usar (ya filtrados por `PAYMENTS_CHECKOUT_METHODS` y credenciales).

### PayPal Developer Dashboard y actividad

Laravel **no crea** aplicaciones en tu cuenta de desarrollador. Para ver credenciales y actividad:

1. Inicia sesión en [PayPal Developer Dashboard](https://developer.paypal.com/dashboard).
2. Abre **Apps & Credentials** y el entorno **Sandbox** (para pruebas).
3. Crea una aplicación REST y copia **Client ID** y **Secret** a `PAYPAL_CLIENT_ID` y `PAYPAL_SECRET` con `PAYPAL_MODE=sandbox`.
4. Las órdenes y capturas aparecen cuando el flujo de la tienda llama a la API de PayPal con esas credenciales; los pagos marcados como simulados **solo en Laravel** no generan actividad en PayPal.

Si el panel parece vacío, comprueba que hayas creado una app y que estés en **Sandbox**, no solo en Live.

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

- [ ] `STRIPE_*`, `PAYPAL_*`, `REDSYS_*` o `REVOLUT_MERCHANT_API_KEY` configurados según los métodos que quieras ofrecer.
- [ ] Webhooks de Stripe/Revolut apuntando a URLs públicas HTTPS y secretos en `.env`.
- [ ] `APP_DEBUG=false`, `PAYMENTS_ALLOW_SIMULATED=false`.
- [ ] `MAIL_*` apuntando a un SMTP o servicio real y `MAIL_FROM_*` correctos.
