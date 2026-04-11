# Checkout UI: quitar banners azules y asegurar visibilidad de Stripe (card)

## GitHub
- (Opcional) enlazar issue de despliegue / UX si existe.

## Problem / goal

1. **Banners azules no deseados:** En `/checkout`, cuando el cliente debe pagar, aparecen **dos bloques grandes de estilo info (azul)** — típicamente `alert alert-info` en `resources/js/Pages/CheckoutPage.jsx` (p. ej. aviso de modo simulado `checkout.payment.simulated_mode_notice`, avisos `paypal_missing_credentials` / `stripe_missing_credentials`, u otros `alert-info` en la sección de pago). El propietario del producto **no quiere volver a ver** esos mensajes prominentes en ese contexto.
2. **Stripe en el selector:** En producción, si `PAYMENTS_CHECKOUT_METHODS` solo incluye `paypal`, **`methods.card`** queda en `false` aunque existan claves Stripe (`PaymentCheckoutService::applyCheckoutMethodWhitelist`). Hay que **documentar y alinear** `.env` de despliegue y documentación para que **tarjeta (Stripe Checkout)** aparezca cuando corresponda, sin depender solo de cambios manuales en servidor.

## High-level instructions for coder

- **UI:** Eliminar o sustituir por un patrón **discreto** (p. ej. texto pequeño neutro, tooltip, o nada) los `alert-info` grandes de la zona de pago del checkout que el PO considera intrusivos. Mantener **i18n** (`ca` / `es`); no dejar strings hardcodeadas. Si se eliminan bloques, eliminar o reutilizar claves de traducción según convenga para no dejar keys muertas sin criterio.
- **Config / docs:** Revisar `config/payments.php`, `.env.example`, y `docs/CONFIGURACION_PAGOS_CORREO.md` (o el doc de pagos que aplique) para dejar claro:
  - `PAYMENTS_CHECKOUT_METHODS` vacío = `card` + `paypal`; para incluir **Stripe (card)** explícitamente: `card,paypal` (o el orden acordado).
  - Variables `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` para entorno real.
- **Verificación:** Con `GET /api/v1/payments/config`, cuando Stripe esté configurado y `card` esté en la whitelist, `data.methods.card` debe ser `true` y el `<select>` de método de pago debe listar la opción de tarjeta (clave de traducción `checkout.payment.card` — no hace falta mostrar la palabra “Stripe” en el label si no es requisito).

## Acceptance criteria

- No hay `alert-info` (u otros banners igual de prominentes en azul/info) en el checkout para los avisos descritos, salvo que el PO apruebe un sustituto mínimo.
- Documentación / ejemplo de entorno reflejan cómo exponer **card (Stripe)** junto a PayPal.
- Tests y build pasan según instrucciones anteriores.

## Implementation summary

- **`CheckoutPage.jsx`:** Reemplazados los `alert alert-info` de la zona de pago (modo simulado, avisos PayPal/Stripe sin credenciales) por párrafos discretos (`text-xs text-base-content/60`). Mensaje de instalación “pago tras presupuesto” pasó de `alert-info` a texto con borde lateral neutro (`text-sm text-base-content/70`, `border-l-2 border-base-300`). Claves i18n existentes reutilizadas.
- **`config/payments.php`:** Comentarios ampliados sobre lista vacía vs `card,paypal` y efecto de whitelist solo-PayPal sobre `methods.card`.
- **`.env.example`:** Ejemplos explícitos para ambos métodos vs solo PayPal.
- **`docs/CONFIGURACION_PAGOS_CORREO.md`:** Sección `PAYMENTS_CHECKOUT_METHODS` ampliada con `card,paypal`, advertencia sobre `paypal`-only + claves Stripe, y omisión/vacío = ambos métodos.

## Testing instructions

1. `php artisan test`
2. `php artisan routes:smoke`
3. `npm run build` (obligatorio tras cambios en `resources/js/`)
4. Manual: en local o staging, abrir `/checkout` con carrito y usuario; confirmar **ausencia** de bloques azules tipo `alert-info` en la sección de pago (texto discreto opcional sigue visible). Con `.env` que incluya `card` en métodos (o vacío) y claves Stripe de prueba, comprobar que el selector lista **tarjeta** y `GET /api/v1/payments/config` devuelve `methods.card: true`.
