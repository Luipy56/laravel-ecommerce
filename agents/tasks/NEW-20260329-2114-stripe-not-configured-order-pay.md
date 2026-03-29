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
