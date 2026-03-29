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
