# Stripe not configured when starting checkout pay

## Source
- **Log:** `storage/logs/laravel.log`
- **Representative lines:** `PaymentProviderNotConfiguredException` / Stripe credentials missing at checkout pay (historical `RuntimeException` at `StripeCheckoutStarter`).

## High-level instructions for coder
- Sin Stripe válido: no tratar como fallo de aplicación en logs; API **422** con `code: payment_method_not_configured`; alinear `payments/config` con reglas de credenciales.

## Coder status
- **Status:** Ready for tester (**UNTESTED** rename applied).

## What was done (implementation + test hardening)
- **Dominio (ya en rama):** `PaymentProviderNotConfiguredException`, `StripeCredentials::areConfigured()`, `StripeCheckoutStarter`, `OrderController` (`checkout` / `pay`), `jsonWhenPaymentStartFailed`, `dontReport` en `bootstrap/app.php`.
- **`tests/Feature/OrderPayConfigurationExceptionTest`:** En Laravel 12 `Log::fake()` ya no aplica al facade de log; se usa `Event::fake([MessageLogged::class])` y se asume que no se despachan logs de nivel error+ ante el 422 esperado. El mock de Stripe debe ser una **subclase de `StripeCheckoutStarter`** para satisfacer el tipo del constructor de `PaymentCheckoutService` (evita 500 por `TypeError`).
- **`database/seeders/ClientContactSeeder`:** Deja de asumir `client_id` 1–3; resuelve `client_id` por `login_email` tras `UserSeeder` (más robusto si los IDs no empiezan en 1).

---

## Testing instructions

### What to verify
- Sin Stripe configurado, `GET /api/v1/payments/config` no ofrece tarjeta cuando no aplica simulación (coherente con credenciales).
- Si se llama a `pay` con tarjeta y el arranque del PSP responde “no configurado”, la API devuelve **422** con `code: payment_method_not_configured` y mensaje traducido; no debe registrarse log de nivel **error** (o equivalente) por ese camino en el test de feature.

### How to test
1. Requiere PHP con **`pdo_sqlite`** (como en CI: extensión `sqlite`). Sin ella la suite de PHPUnit con `DB_CONNECTION=sqlite` no arranca.
2. **`php artisan test`** — debe pasar toda la suite (incluye `OrderPayConfigurationExceptionTest`, `StripeCheckoutStarterTest`, `CheckoutPaymentConfigTest`).
3. **`php artisan routes:smoke`** — ningún GET con **500**.

### Pass/fail criteria
| Criterio | Pass |
|----------|------|
| `php artisan test` | Sin fallos |
| `routes:smoke` | Sin HTTP 500 en GET comprobados |
| Stripe no configurado en pay | 422 + `payment_method_not_configured`; el feature test no observa `MessageLogged` de nivel error+ |

---

## Test report

1. **Date/time (UTC) y ventana de logs:** Inicio verificación **2026-03-30 09:25:15 UTC**; fin inmediatamente tras `routes:smoke`. No se revisó `storage/logs/laravel.log` (criterios cubiertos por tests automatizados).

2. **Entorno:** PHP **8.3.6** (CLI), extensiones **`pdo_sqlite` / `sqlite3`** presentes. Rama **`agentdevelop`** (post-`./scripts/git-sync-agent-branch.sh`). `APP_ENV` no forzado (suite PHPUnit con configuración por defecto del proyecto).

3. **Qué se probó (según “What to verify”):** Coherencia de `payments/config` sin Stripe cuando no aplica simulación; respuesta **422** con `payment_method_not_configured` en `pay` con tarjeta cuando el PSP no está configurado; ausencia de logs de error por ese camino en el feature test; humo de rutas GET.

4. **Results:**
   - **`php artisan test` sin fallos:** **PASS** — `Tests: 40 passed (192 assertions)`; incluye `OrderPayConfigurationExceptionTest`, `StripeCheckoutStarterTest`, `CheckoutPaymentConfigTest`.
   - **`routes:smoke` sin HTTP 500:** **PASS** — salida: `All checked GET routes returned a non-500 status.`
   - **Stripe no configurado en pay (422 + código; sin `MessageLogged` error+ en el test):** **PASS** — `OrderPayConfigurationExceptionTest` ✓ `order pay does not report log when stripe reports not configured vi…`

5. **Overall:** **PASS** (todos los criterios cumplidos).

6. **Product owner feedback:** El flujo de pago con tarjeta sin Stripe configurado queda acotado a una respuesta API predecible (**422** con código de negocio) y los tests confirman que no se dispara registro de error por ese camino. La configuración expuesta en `payments/config` y los arranques de PSP siguen alineados con las reglas de credenciales descritas en la tarea.

7. **URLs probadas:** **N/A — no browser** (verificación solo CLI).

8. **Relevant log excerpts:** No aplicable a `laravel.log` en esta pasada. Evidencia mínima de consola:

```
Tests:    40 passed (192 assertions)
All checked GET routes returned a non-500 status.
```
