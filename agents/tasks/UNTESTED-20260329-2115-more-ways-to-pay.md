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
