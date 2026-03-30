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

## Coder notes (implementation)
- **`PaymentCheckoutService`:** el primer argumento `$stripe` pasa a estar tipado como **`PaymentCheckoutStarter`** (contrato), no como `StripeCheckoutStarter`.
- **`AppServiceProvider::register()`:** binding contextual `when(PaymentCheckoutService::class)->needs(PaymentCheckoutStarter::class)->give(StripeCheckoutStarter::class)` para que en runtime se resuelva la implementación Stripe al construir el servicio.
- **`OrderPayConfigurationExceptionTest`:** el mock vuelve a ser una clase anónima que **solo implementa** `PaymentCheckoutStarter` (registrada vía `bind(StripeCheckoutStarter::class, …)`), verificando que ya no hay `TypeError` y se mantiene el **422** con `payment_method_not_configured` y la aserción sobre `MessageLogged`.

---

## Testing instructions

### What to verify
- No `TypeError` al resolver `PaymentCheckoutService` cuando el test sustituye `StripeCheckoutStarter` por un doble que implementa solo `PaymentCheckoutStarter`.
- Comportamiento de pago con Stripe no configurado: **422**, `code: payment_method_not_configured`, sin logs de nivel error+ despachados en el escenario del test.
- Rutas GET no devuelven **500** tras el cambio en el service provider.

### How to test
1. `php artisan test` (toda la suite; como mínimo `--filter=OrderPayConfigurationExceptionTest` y pruebas bajo `Tests\Feature` relacionadas con pagos/checkout).
2. `php artisan routes:smoke`
3. (Opcional) Tras un flujo manual de `POST /api/v1/orders/{id}/pay` con `payment_method=card`, comprobar que no aparece en log el `TypeError` del constructor de `PaymentCheckoutService`.

### Pass/fail criteria
- **PASS:** `php artisan test` termina con código **0**; `php artisan routes:smoke` indica que ninguna ruta GET comprobada devolvió **500**.
- **FAIL:** cualquier fallo en tests, **500** en smoke, o reaparición del `TypeError` en el constructor de `PaymentCheckoutService` para `$stripe`.
