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

## Implementation summary

- **Nuevo estado de comanda:** `Order::STATUS_AWAITING_PAYMENT` (`awaiting_payment`). Se asigna en `POST /api/v1/orders/checkout` cuando el método de pago es **PayPal**, y en `POST /api/v1/orders/{id}/pay` al iniciar un pago PayPal. Card/Stripe y checkout simulado con tarjeta siguen usando `pending` como antes.
- **Tras cobro exitoso:** `PaymentCompletionService::markSucceeded()` pasa la comanda de `awaiting_payment` a `pending` (cola habitual). Los correos de “pago confirmado” siguen disparándose solo vía `markSucceeded` (sin cambio).
- **Factura:** `GET /api/v1/orders/{order}/invoice` responde **403** si no hay pago con éxito (`hasSuccessfulPayment()`). La lista de comandes ya no muestra el enlace a factura sin pago.
- **UI:** Aviso informativo en ficha de comanda cuando l’estat és `awaiting_payment` i encara no hi ha pagament; traduccions **ca/es** (també `lang/ca.json` / `lang/es.json` per PDF si aplica).
- **Admin:** Filtre i edició de comandes inclouen `awaiting_payment`; `trash/diagramZero.dbml` actualitzat.
- **Proves:** `CheckoutPaymentConfigTest` (estat després del checkout PayPal), `PayPalPaymentTest` (captura → `pending`, factura 403 sense pagament).

## Acceptance criteria

- Cerrar PayPal sin pagar **no** deja el pedido presentado al cliente como “ya pagado” / confirmado en el sentido reportado (definir criterio exacto en copy y estado).
- Tras pago real en sandbox, el pedido refleja cobro conforme a la implementación elegida.
- Suite automatizada actualizada; sin regresiones en Stripe u otros métodos si comparten modelos de pedido.

## Testing instructions

1. `php artisan test` — ha de passar (incloent `PayPalPaymentTest`, `CheckoutPaymentConfigTest`, `PaymentWebhookTest`).
2. `php artisan routes:smoke` — cap resposta 500 en GET.
3. `npm run build` — compila sense errors (canvis a `resources/js/`).
4. **Manual (sandbox PayPal):** Checkout amb PayPal → tancar finestra sense pagar → comanda amb estat “Pendent de pagament” / “Pendiente de pago”, sense factura ni percepció de pagament completat; completar pagament → estat `pending`, `has_payment` cert, factura accessible.
5. **Regressió Stripe / targeta simulada:** checkout amb targeta en entorn amb pagament simulat → comanda `pending` i correu de confirmació com abans.
