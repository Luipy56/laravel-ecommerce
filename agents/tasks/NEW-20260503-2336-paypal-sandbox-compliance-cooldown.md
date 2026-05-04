# PayPal sandbox: continuar tras cooldown del compliance

## Contexto

La sesión del 3-4 de mayo dejó **Stripe completamente operativo** y el código de PayPal arreglado, pero la cuenta business sandbox quedó marcada por el sistema anti-fraude de PayPal tras múltiples intentos fallidos. Cualquier transacción nueva (incluso con importes válidos como 10.01 €, cuenta compradora ES, invoice únicos) responde con "Esta transacción se ha rechazado en cumplimiento de las normativas internacionales".

El error **NO es del código**: la petición a `POST https://api-m.sandbox.paypal.com/v2/checkout/orders` devuelve `status: CREATED` correctamente, pero al aprobar en la UI de PayPal se rechaza por el risk engine de PayPal.

## Lo que ya funciona (no tocar)

- **Stripe** completo: claves correctas (`STRIPE_KEY` / `STRIPE_SECRET` / `STRIPE_WEBHOOK_SECRET`), CLI listener documentado, métodos disponibles en checkout.
- **Frontend PayPal**: botones inline renderizados correctamente, popup oficial del SDK, capture en `onApprove`, safety net en `paypal_return`.
- **Backend PayPal**: `invoice_id` ahora único por intento (`ORD-{order_id}-PAY-{payment_id}` — fix en commit `6d76350`).
- **Demo skip checkbox**: visible y funcional con `CHECKOUT_DEMO_SKIP_PAYMENT=true` en `.env`.

## Pasos para mañana

### 1. Verificar si la cuenta business se ha "enfriado" (esperar ~12-24h suele bastar)

```bash
docker exec laravel-ecommerce-app-1 php artisan paypal:test-credentials
```

Debe seguir devolviendo `PayPal OAuth OK`. Si la cuenta está temporalmente bloqueada por riesgo, OAuth puede seguir funcionando pero los pagos se rechazan.

### 2. Crear un pedido de prueba realista

```bash
# Reset limpio si quieres datos sin pagos contaminados:
docker exec laravel-ecommerce-app-1 php artisan migrate:fresh --seed
```

Logueate como cliente seed (`maria.garcia@example.com` / `password` u otro) y haz un carrito de **20-50 €** con un producto normal del seeder.

### 3. Probar PayPal con cuenta compradora **distinta** a las usadas el 3 de mayo

Las cuentas usadas y posiblemente flaggeadas:
- `sb-eynl450844794@personal.example.com` (creada nueva ese día y usada para muchos intentos)
- `sb-eixqn50191370@personal.example.com` (probada al final, también falló)

Cuentas Personal ES disponibles que **no** se usaron:
- `sb-0qf0x50516940@personal.example.com`

Si todas siguen flaggeadas, **crea una cuenta Personal nueva** desde el dashboard de PayPal con país `ES`, moneda `EUR`, balance inicial > 100 €, y **desactiva "Payment Review"** en sus settings.

### 4. Si tras 24h sigue rechazando con cualquier cuenta

Significa que la **business** sandbox (`sb-sthw850181814@business.example.com`) está flaggeada del lado del merchant. Crea una **nueva business sandbox** desde el dashboard:

1. [developer.paypal.com/dashboard](https://developer.paypal.com/dashboard) → Apps & Credentials → **Create App**
2. App Type: **Merchant**, Sandbox Account: cuenta business nueva (créala en Sandbox Accounts si no existe).
3. Copia el nuevo **Client ID** y **Secret** y reemplaza en `.env`:
   ```env
   PAYPAL_CLIENT_ID=<nuevo_client_id>
   PAYPAL_SECRET=<nuevo_secret>
   PAYPAL_MODE=sandbox
   ```
4. Recrea el contenedor (sin esto Docker mantiene los valores antiguos del entorno):
   ```bash
   docker compose up -d --force-recreate app
   ```
5. Verifica con:
   ```bash
   curl -s http://localhost:8080/api/v1/payments/config | python3 -m json.tool
   ```
   `methods.paypal` debe ser `true` y `paypal_missing_credentials` debe ser `false`.

### 5. Para diagnóstico, mira la API call directa en PayPal Dashboard

Si vuelve a fallar con cuentas y credenciales nuevas, ve a:
[developer.paypal.com/dashboard/api-calls/sandbox](https://developer.paypal.com/dashboard/api-calls/sandbox)

Ahí verás la request/response real. Si `status: CREATED` pero la aprobación falla en la UI, es 100% problema del risk engine de PayPal sandbox.

## Mejora opcional: logging del capture

Cuando vuelva a funcionar, considera añadir logging detallado en `app/Http/Controllers/Api/PayPalPaymentController.php` para que la próxima vez veamos códigos PayPal específicos (`COMPLIANCE_VIOLATION`, `INSTRUMENT_DECLINED`, `PAYER_ACCOUNT_RESTRICTED`, etc.) directamente en `storage/logs/laravel.log`.

```php
} catch (Throwable $e) {
    report($e);
    Log::warning('paypal.capture.failed', [
        'paypal_order_id' => $validated['paypal_order_id'],
        'payment_id' => $validated['payment_id'],
        'error' => $e->getMessage(),
    ]);
    // ...
}
```

## Notas operativas

- **Docker y `.env`:** Tras CUALQUIER cambio en `.env`, `docker compose restart app` **NO basta** — los valores del entorno quedan congelados desde el primer `up`. Usa siempre `docker compose up -d --force-recreate app`.
- **Stripe CLI:** Si necesitas webhooks reales, ejecuta el comando de `docs/CONFIGURACION_PAGOS_CORREO.md` (sección "Stripe CLI: webhooks en desarrollo local"). En desarrollo, `POST /api/v1/payments/stripe/checkout/confirm` ya completa el pago al volver de Stripe.
- **Importes mínimos:** El sandbox de PayPal rechaza < ~1 € como anti-fraude. Carrito de prueba debe ser ≥ 5 €.

## Testing instructions (cuando esté arreglado)

1. `docker exec laravel-ecommerce-app-1 php artisan paypal:test-credentials` → `OAuth OK`.
2. `curl -s http://localhost:8080/api/v1/payments/config` → `methods.paypal: true`, `paypal_missing_credentials: false`, `paypal_mode: sandbox`.
3. Login como cliente con carrito ≥ 5 €. Ir a `/checkout` → seleccionar PayPal → confirmar pedido.
4. En la página del pedido, clic en el botón PayPal → popup oficial sandbox → loguear con cuenta personal ES sandbox no flaggeada → aprobar pago.
5. Esperar redirect a `/orders/{id}?payment=paypal_return` → ver toast de pago confirmado.
6. Verificar en BD: `psql ... -c "SELECT id, order_id, status FROM payments WHERE gateway='paypal' ORDER BY id DESC LIMIT 1"` → `status: succeeded`.
7. Verificar en [PayPal Dashboard sandbox](https://developer.paypal.com/dashboard/api-calls/sandbox) que la `capture` aparece como `COMPLETED`.
