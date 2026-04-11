# PayPal checkout: no dar por confirmado el pedido hasta que el pago esté capturado / aprobado

## GitHub
- (Opcional) enlazar issue #12 u otra si el flujo de pagos está relacionado.

## Problem / goal

**Síntoma actual (reporte PO):** Al pulsar **Finalizar pedido**, se abre un modal de **Confirmar**; tras confirmar, el flujo lleva a PayPal. Si el usuario **cierra la ventana de PayPal** y vuelve al e-commerce, el pedido aparece **como confirmado** aunque **no haya pagado** ni completado el flujo como comprador. En el dashboard de PayPal el pedido puede figurar en estado **CREATED** (orden creada, sin pagador asociado aún) — coherente con la API de Orders v2 antes de `approve` + `capture`.

**Objetivo de negocio:** El cliente **no debe** ver el pedido como definitivamente confirmado/pagado **hasta** que PayPal haya **aprobado** el pago y el backend haya **capturado** (o equivalente verificado) el cobro. El orden lógico deseado: **primero** completar y confirmar el pago en PayPal **luego** reflejar en el e-commerce el estado correcto (y emails/transacciones acordes).

**Referencia técnica (sandbox, ejemplo real):** Creación de orden PayPal con `intent: CAPTURE`, `purchase_units` con `reference_id` / `custom_id` ligados al `payment_id` interno, `status: CREATED`, y links `approve` vs `capture`. Hasta que el comprador no aprueba en PayPal y el backend no ejecuta **capture** (o el webhook/flujo acordado confirma el pago), el pedido no debe tratarse como cobrado.

## High-level instructions for coder

- **Auditar flujo actual:** `OrderController::checkout` (convierte carrito → `Order::KIND_ORDER`, `STATUS_PENDING`, crea `Payment` PENDING, luego `PaymentCheckoutService::start` para PayPal). Front: `resources/js/Pages/CheckoutPage.jsx` (`ConfirmModal` → `doCheckout` → redirect `approval_url` o `PayPalInlineButtons`). `PayPalCheckoutStarter`, `PayPalPaymentController` capture, webhooks si existen.
- **Diseñar cambio mínimo viable** que cumpla:
  - No mostrar al usuario “pedido confirmado” / no enviar comunicaciones de pedido pagado hasta **capture exitoso** o reglas explícitas de “pendiente de pago”.
  - Posibles direcciones (elegir con criterio y tests): nuevo estado de pedido tipo **awaiting_payment** / **payment_pending** hasta captura; o **no** convertir carrito en pedido hasta redirect de retorno exitoso (más invasivo); o mantener pedido pero UI y `hasSuccessfulPayment()` estrictos.
- **Sincronizar** con `Order::hasSuccessfulPayment()`, emails de pedido, página `/orders/:id`, y tests en `tests/Feature/PayPalPaymentTest.php` (y relacionados).
- **Seguridad:** El estado pagado debe basarse en **servidor** (respuesta de capture o webhook verificado), no solo en query params del return URL.
- **i18n:** Cualquier mensaje nuevo al usuario en **ca** y **es**.

## Testing instructions

1. `php artisan migrate:fresh --seed` **solo** si el cambio toca esquema o seeders; si no, al menos `php artisan test`.
2. `php artisan test` — actualizar o añadir tests que cubran: pedido **no** considerado pagado con solo orden PayPal CREATED; tras **capture** simulado/mocked, sí.
3. `php artisan routes:smoke`
4. `npm run build` si cambia el front.
5. **Manual (sandbox PayPal):** Flujo completo: iniciar checkout PayPal → **cerrar** ventana sin pagar → verificar que el pedido **no** aparece como pagado / no genera la percepción errónea descrita. Luego flujo feliz hasta capture y verificar pedido pagado.

## Acceptance criteria

- Cerrar PayPal sin pagar **no** deja el pedido presentado al cliente como “ya pagado” / confirmado en el sentido reportado (definir criterio exacto en copy y estado).
- Tras pago real en sandbox, el pedido refleja cobro conforme a la implementación elegida.
- Suite automatizada actualizada; sin regresiones en Stripe u otros métodos si comparten modelos de pedido.
