# PayPal checkout: credentials, UX, and end-to-end verification

## Problem / goal

Checkout should offer **PayPal** when configured and let a **real buyer** complete payment in **sandbox** (then **live** when keys are production). The app already integrates PayPal REST + JS SDK; this task validates configuration, documents the operator steps, and confirms the user flow.

**Security note:** Users **sign in to PayPal** via the official PayPal button / SDK flow. Do **not** collect PayPal passwords or full card data in custom site forms.

## References (read first)

- `config/payments.php` — `PAYMENTS_CHECKOUT_METHODS`, simulated mode behavior.
- `app/Services/Payments/PaymentCheckoutService.php` — when PayPal is shown; PayPal is never “simulated” without credentials.
- `resources/js/Pages/CheckoutPage.jsx` — method select, PayPal UI.
- `docs/CONFIGURACION_PAGOS_CORREO.md` — env vars: `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_MODE` (`sandbox` / `live`).
- `php artisan paypal:test-credentials` — optional OAuth check after `.env` is set.

## High-level instructions for coder

1. Document or improve **operator setup**: minimal `.env` for sandbox, `PAYMENTS_CHECKOUT_METHODS=paypal` (or combined list), `PAYMENTS_ALLOW_SIMULATED=false` when testing real PayPal.
2. Verify **`GET /api/v1/payments/config`** returns `methods.paypal: true` with valid credentials.
3. Manual E2E: logged-in user, non-empty cart, **`/checkout`**, select PayPal, complete sandbox payment; order/payment state updates as designed.
4. Add or extend **automated tests** only where stable (HTTP fakes/mocks); avoid flaking against live PayPal in CI unless already patterned in repo.
5. i18n: any new user-visible strings under **`resources/js/locales/ca.json`** and **`es.json`**.

## Acceptance criteria

- Sandbox PayPal checkout works from **`/checkout`** with documented env on a fresh **`migrate:fresh --seed`** dev DB.
- **`php artisan test`** passes; if payment tests exist, they remain green.
- **`npm run build`** passes if JSX changed.

## Implementation notes (coder)

- Extended **`docs/CONFIGURACION_PAGOS_CORREO.md`** with subsection **“Flujo E2E PayPal sandbox (checklist operador)”**: `.env` mínimo (`PAYMENTS_CHECKOUT_METHODS=paypal`, `PAYMENTS_ALLOW_SIMULATED=false`, `PAYPAL_*`), `migrate:fresh --seed`, `paypal:test-credentials`, comprobación de `GET /api/v1/payments/config`, pasos en navegador con usuario seeder y cuenta compradora sandbox (recordatorio de seguridad: login solo vía SDK PayPal).
- Añadido test **`test_payments_config_paypal_only_with_credentials_and_no_simulation`** en **`tests/Feature/CheckoutPaymentConfigTest.php`** para fijar el contrato JSON del escenario operador (solo PayPal, sin simulación).

## Testing instructions (tester)

1. **`php artisan test`** — debe pasar (incluye `CheckoutPaymentConfigTest` y `PayPalPaymentTest`).
2. **`php artisan routes:smoke`** — ningún GET con HTTP 500.
3. **Manual (con credenciales sandbox reales en `.env`)**:
   - `php artisan migrate:fresh --seed`
   - Seguir la checklist en **`docs/CONFIGURACION_PAGOS_CORREO.md`** → *Flujo E2E PayPal sandbox*.
   - Confirmar `GET /api/v1/payments/config`: `data.methods.paypal === true`, `data.paypal_missing_credentials === false`.
   - En **`/checkout`** con carrito no vacío: flujo PayPal hasta captura y estado coherente en pedido/pago.
4. **No hubo cambios en `resources/js/`** — `npm run build` no es obligatorio para esta tarea salvo verificación global del pipeline.
