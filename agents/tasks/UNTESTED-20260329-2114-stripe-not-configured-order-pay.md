# Stripe not configured when starting checkout pay

## Source
- **Log:** `storage/logs/laravel.log`
- **UTC window reviewed:** desde la última marca en `agents/001-log-reviewer/time-of-last-review.txt` (2026-03-27T18:14Z) hasta este pase; no hay entradas con timestamp posterior a **2026-03-22** en la cola del fichero, pero el incidente sigue siendo el error más reciente **de esta app** en el log.
- **Representative lines:**
  - `[2026-03-22 20:55:28] local.ERROR: Stripe is not configured (STRIPE_SECRET).` … `RuntimeException` at `app/Services/Payments/Stripe/StripeCheckoutStarter.php:22`, invoked desde `PaymentCheckoutService.php` → `OrderController::pay()`.

## High-level instructions for coder
- Reproducir el flujo de pago que selecciona Stripe sin `STRIPE_SECRET` (o con método expuesto en UI cuando el backend no está listo).
- Decidir comportamiento deseado: no ofrecer Stripe en `payments/config` si las credenciales no son válidas, o responder API/UX con error claro (4xx) sin `local.ERROR` ruidoso, según política del proyecto y `PAYMENTS_ALLOW_SIMULATED`.
- Alinear con documentación de pagos y con el issue **#5** solo en la medida en que compartan configuración de métodos; este **NEW** es el fallo concreto del log, no sustituye el alcance funcional del issue.

## Coder status
- **Status:** Implementation complete (handoff to tester).

## What was done
- **`App\Exceptions\PaymentProviderNotConfiguredException`:** error de dominio cuando faltan claves Stripe (u otro PSP que la use) al arrancar checkout; no debe tratarse como fallo de aplicación en logs.
- **`App\Services\Payments\Stripe\StripeCredentials`:** `areConfigured()` con la misma regla que el starter (solo strings no vacíos para `STRIPE_SECRET` / `STRIPE_KEY`), usada en `PaymentCheckoutService::methodHasRealProviderCredentials` y `paymentMethodsBaseAvailability` para alinear `payments/config` y guards con `StripeCheckoutStarter`.
- **`StripeCheckoutStarter`:** lanza `PaymentProviderNotConfiguredException` en lugar de `RuntimeException` genérico cuando faltan credenciales.
- **`OrderController`:** en `checkout` y `pay`, captura explícita de `PaymentProviderNotConfiguredException` **sin** `report()`; `jsonWhenPaymentStartFailed` devuelve **422** con `code: payment_method_not_configured` y mensaje traducido (`shop.payment.method_unavailable`).
- **`bootstrap/app.php`:** `dontReport(PaymentProviderNotConfiguredException::class)` por si la excepción escapara del controlador.
- **Tests:** `tests/Unit/StripeCheckoutStarterTest.php`; `tests/Feature/OrderPayConfigurationExceptionTest.php` (mock de `StripeCheckoutStarter` + `Log::fake()` para asegurar que no se registra nada ante este caso).

---

## Testing instructions

### What to verify
- Sin Stripe configurado, el storefront sigue sin ofrecer tarjeta cuando no aplica simulación (`paymentMethodsAvailability` / `GET /api/v1/payments/config`).
- Si por cualquier motivo se llega a `start()` con tarjeta y credenciales ausentes, la API responde **422** con mensaje claro y **`code: payment_method_not_configured`**, y **no** se escribe línea `local.ERROR` en el log por ese motivo.

### How to test
1. **`php artisan test`** — debe pasar la suite (incluye `StripeCheckoutStarterTest`, `OrderPayConfigurationExceptionTest`, `CheckoutPaymentConfigTest`). Si el PHP local no tiene `pdo_sqlite`, ejecutar en un entorno con SQLite o la BD de tests del proyecto.
2. **`php artisan routes:smoke`** — ninguna ruta GET con **500**.
3. **Manual (opcional):** Con usuario autenticado y pedido pendiente, forzar escenario de pago con tarjeta sin `STRIPE_*` y `PAYMENTS_ALLOW_SIMULATED=false`; comprobar **422** y ausencia de nueva entrada ERROR en `storage/logs/laravel.log` atribuible a “Stripe is not configured”.

### Pass/fail criteria
| Criterio | Pass |
|----------|------|
| `php artisan test` completo | Sin fallos (o documentar imposibilidad por falta de `pdo_sqlite`). |
| `routes:smoke` | Sin HTTP 500 en GET comprobados. |
| Comportamiento Stripe no configurado | 422 + `payment_method_not_configured`; sin `report()` / ERROR de log para `PaymentProviderNotConfiguredException`. |
