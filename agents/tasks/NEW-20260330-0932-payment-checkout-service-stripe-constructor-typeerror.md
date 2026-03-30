# PaymentCheckoutService: TypeError on Stripe constructor (anonymous PaymentCheckoutStarter)

## Source
- **Log:** `storage/logs/laravel.log`
- **UTC window (approx.):** desde la pasada revisión 001 (`2026-03-30T09:18Z`) hasta el barrido de esta corrida; entradas con marca local en log **`[2026-03-30 …]`**.
- **Representative lines:**
  - `testing.ERROR: App\Services\Payments\PaymentCheckoutService::__construct(): Argument #1 ($stripe) must be of type App\Services\Payments\Stripe\StripeCheckoutStarter, App\Contracts\Payments\PaymentCheckoutStarter@anonymous given, called in ... Container.php on line 1168 at ... PaymentCheckoutService.php:18`
  - Stack incluye `tests/Feature/OrderPayConfigurationExceptionTest.php` vía `postJson` (entorno `testing`).

## High-level instructions for coder
- Revisar el **constructor tipado** de `PaymentCheckoutService` frente a lo que el contenedor inyecta en tests y en runtime (binding de `PaymentCheckoutStarter` / `StripeCheckoutStarter`).
- Alinear con el contrato: o bien el primer argumento acepta la **interfaz** `PaymentCheckoutStarter` de forma consistente en todo el registro del contenedor, o los tests y factories deben registrar una **implementación concreta** que sea subclase de `StripeCheckoutStarter` (como ya documentó el cierre previo del incidente Stripe no configurado).
- Localizar mocks anónimos o `bind` que devuelvan `PaymentCheckoutStarter@anonymous` y sustituirlos por un doble compatible con el tipo real del constructor, sin romper el comportamiento 422 esperado cuando Stripe no está configurado.
- Ejecutar **`php artisan test`** (especialmente `OrderPayConfigurationExceptionTest` y pruebas de checkout/pagos) y confirmar que no quedan `TypeError` en log para ese path.
