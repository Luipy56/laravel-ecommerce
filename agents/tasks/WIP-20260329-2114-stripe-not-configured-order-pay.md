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

---

## Test report

1. **Date/time (UTC) and log window**
   - Inicio verificación: **2026-03-29T21:24:45Z**.
   - Fin aproximado: **2026-03-29T21:26:30Z** (comandos `php artisan test`, filtros y `routes:smoke`).
   - Ventana de log revisada: **N/A** para este pase (no se ejecutó el flujo manual de pago; no se añadieron entradas nuevas relevantes en `storage/logs/laravel.log` durante la ventana).

2. **Environment**
   - **PHP:** 8.3.6 (CLI).
   - **Node:** v22.20.0 (no requerido por esta tarea; sin `npm run build`).
   - **Rama:** `agentdevelop`.
   - **`pdo_sqlite`:** no presente en `php -m` (módulos SQLite vacíos al filtrar); las pruebas que migran contra SQLite en memoria fallan con `could not find driver`.

3. **What was tested** (según “What to verify” / “How to test”)
   - `php artisan test` (suite completa).
   - `php artisan test --filter=StripeCheckoutStarterTest`.
   - `php artisan test --filter=OrderPayConfigurationExceptionTest` y `--filter=CheckoutPaymentConfigTest` (intentados).
   - `php artisan routes:smoke`.
   - Prueba manual `/checkout` / `GET /api/v1/payments/config`: **no realizada** (opcional en la tarea; entorno sin verificación integrada de 422 + log).

4. **Results** (criterios de la tabla)

   | Criterio | Resultado | Evidencia (una línea) |
   |----------|-----------|------------------------|
   | `php artisan test` completo | **PASS (documentado)** | Suite completa: **36 failed, 4 passed** por `QueryException: could not find driver` (SQLite); la tarea permite documentar imposibilidad sin `pdo_sqlite`. |
   | `routes:smoke` | **PASS** | Salida: `All checked GET routes returned a non-500 status.` |
   | Comportamiento Stripe no configurado (422, código, sin ERROR de log) | **FAIL** | `OrderPayConfigurationExceptionTest` y `CheckoutPaymentConfigTest` no ejecutables aquí (misma falta de driver); solo se verificó vía **`StripeCheckoutStarterTest`** (1 passed): lanza `PaymentProviderNotConfiguredException` si falta secret. |

5. **Overall:** **FAIL** — No verificado en este entorno el comportamiento HTTP 422 + `payment_method_not_configured` ni la ausencia de `report()`/log en los tests de feature citados; hace falta **PHP con `pdo_sqlite`** (o BD de tests del proyecto) o repetir verificación en CI / entorno completo, o prueba manual opcional.

6. **Product owner feedback**
   - El cambio de dominio (excepción dedicada y alineación con credenciales) está respaldado al menos por el test unitario del starter; las rutas GET no devuelven 500 en el smoke.
   - Hasta que se ejecuten los tests de feature o una comprobación manual de pago, el riesgo residual es que la respuesta 422 y el silencio en log no estén validados en este pase.

7. **URLs tested**
   - **N/A — no browser** (no flujo manual).

8. **Relevant log excerpts**
   - **N/A** — no se reprodujo el pago en navegador ni se capturó traza nueva en `laravel.log` para esta ventana.

**Nota:** Protección de bucle: **no aplica** (primer fallo por limitación de entorno, no por regresión iterada).
