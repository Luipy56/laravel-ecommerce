# More ways to pay

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/5

## Problem / goal
Hoy existe pago con PayPal; se piden medios adicionales (Revolut y tarjeta de crédito “normal”), alineados con el stack de pagos del proyecto.

## High-level instructions for coder
- Revisar `docs/CONFIGURACION_PAGOS_CORREO.md` y la integración actual (PayPal, Stripe u otros) para ver qué ya está cableado y qué falta.
- Proponer e implementar rutas de pago adicionales de forma coherente con `PaymentCheckoutService` y la UI de checkout (React), respetando simulación/sandbox donde aplique.
- Asegurar que `GET /api/v1/payments/config` y el selector de métodos reflejen los nuevos proveedores cuando estén configurados.
- Añadir o actualizar variables de entorno documentadas; no commitear claves. Verificar flujo con carrito y usuario autenticado según reglas de testing del repo.

## Implementation summary
- **Ya existente:** Tarjeta (Stripe Payment Element), Revolut (orden Merchant API + `checkout_url` + redirección), PayPal, Bizum/Redsys; `PaymentCheckoutService`, webhooks y UI en `CheckoutPage` / `OrderDetailPage`.
- **Añadido:** Banderas `stripe_missing_credentials` y `revolut_missing_credentials` en `PaymentConfigController` y en la respuesta de `OrderController::show` (paridad con `paypal_missing_credentials` cuando el método está en `PAYMENTS_CHECKOUT_METHODS` pero faltan credenciales y no aplica simulación). Avisos en checkout y ficha de pedido (i18n ca/es). Etiqueta de método `card` más explícita. Documentación en `docs/CONFIGURACION_PAGOS_CORREO.md`. Tests en `CheckoutPaymentConfigTest`.

## Testing instructions
1. **`php artisan test`** — incluye `tests/Feature/CheckoutPaymentConfigTest.php` (nuevos casos `stripe_missing_credentials` / `revolut_missing_credentials`). Si el entorno no tiene `pdo_sqlite`, ejecutar en un PHP con SQLite o la BD de tests del proyecto.
2. **`php artisan routes:smoke`** — sin respuestas 500 en rutas GET.
3. **`npm run build`** — obligatorio si se toca el front; en este entorno el build puede fallar si falta la dependencia `zod` en `node_modules` (error preexistente al resolver `validation/issueToMessage.js`); en máquina del tester: `npm ci` y repetir.
4. **Manual checkout / pedido:** Con `PAYMENTS_ALLOW_SIMULATED=false` y `PAYMENTS_CHECKOUT_METHODS=card` sin `STRIPE_*`, comprobar `GET /api/v1/payments/config`: `data.methods.card === false`, `data.stripe_missing_credentials === true` y el aviso informativo en `/checkout`. Con `REVOLUT_MERCHANT_API_KEY` vacío y `PAYMENTS_CHECKOUT_METHODS=revolut`, misma idea para `revolut_missing_credentials`.
5. **Con claves reales:** Con `STRIPE_KEY`+`STRIPE_SECRET`, flujo checkout → bloque Stripe en página; con `REVOLUT_MERCHANT_API_KEY`, redirección a hosted checkout de Revolut tras `POST orders/checkout`.

Comentario en GitHub **#5** publicado con el resumen técnico.

---

## Test report

1. **Date/time (UTC) and log window:** 2026-03-30 10:21–10:23 UTC (ejecución de comandos). Ventana de log: sin errores nuevos atribuibles a esta verificación; `storage/logs/laravel.log` contiene trazas históricas de tests (no se usó como criterio de fallo).

2. **Environment:** PHP 8.3.6, Node v22.20.0, rama `agentdevelop`, commit `43ab22c`. `APP_ENV` no alterado para la suite (tests con SQLite en memoria según configuración del proyecto).

3. **What was tested (from “What to verify” / Testing instructions):** `php artisan test` (incl. `CheckoutPaymentConfigTest`), `php artisan routes:smoke`, `npm run build`; equivalencia funcional del apartado manual 4 respecto a `GET /api/v1/payments/config` vía tests que fijan `config()` como en las instrucciones. No navegador. Punto 5 (claves reales / PSP) no ejecutado (N/A).

4. **Results:**
   - **1. `php artisan test`:** **PASS** — `Tests: 42 passed (201 assertions)`; incluye `test_payments_config_stripe_missing_credentials_when_whitelisted_without_keys_and_no_simulation` y `test_payments_config_revolut_missing_credentials_when_whitelisted_without_key_and_no_simulation`.
   - **2. `php artisan routes:smoke`:** **PASS** — salida: `All checked GET routes returned a non-500 status.`
   - **3. `npm run build`:** **PASS** — `vite build` completado (`✓ built in 3.61s`; aviso CSS `@property` no bloqueante).
   - **4. Manual checkout / pedido (API + UI):** **PASS (API)** / **N/A (UI en navegador)** — las aserciones JSON de `data.methods.card`, `data.stripe_missing_credentials`, `data.methods.revolut`, `data.revolut_missing_credentials` están cubiertas por los tests citados; no se abrió `/checkout` en navegador para validar el aviso visual.
   - **5. Con claves reales:** **N/A** — fuera de alcance en este entorno (sin PSP reales).

5. **Overall:** **PASS** (criterios automatizados al 100 %; UI de checkout no comprobada en navegador).

6. **Product owner feedback:** La API de configuración de pagos expone correctamente los flags de credenciales faltantes para tarjeta y Revolut cuando el método está permitido y no hay simulación, alineado con lo pedido para el selector y mensajes. Conviene una pasada manual breve en `/checkout` y ficha de pedido con los `.env` de staging para confirmar textos y avisos en catalán y castellano. Los flujos con Stripe y Revolut en producción siguen dependiendo de claves válidas en el despliegue.

7. **URLs tested:** **N/A — no browser** (solo peticiones HTTP internas vía PHPUnit).

8. **Relevant log excerpts (last section):** Evidencia principal = salida de consola, no log de aplicación:
   - `Tests:    42 passed (201 assertions)`
   - `All checked GET routes returned a non-500 status.`
   - `✓ built in 3.61s`
