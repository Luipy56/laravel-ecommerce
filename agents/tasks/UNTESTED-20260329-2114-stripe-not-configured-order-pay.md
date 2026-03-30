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
