# Mobile view

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/4

## Problem / goal
No todas las páginas tienen una buena vista móvil y no hay guías claras en el proyecto para implementar layouts responsive consistentes.

## High-level instructions for coder
- Inventariar páginas públicas y admin que se rompen o quedan incómodas en viewports pequeños (navegación, tablas, formularios, checkout).
- Aplicar patrones existentes: Tailwind 4, daisyUI 5, componentes compartidos (`PageTitle`, `AdminLayout`, listas admin con toolbar en una fila, etc.).
- Donde ayude al equipo, añadir una sección breve en documentación interna (por ejemplo en `docs/` o comentario en reglas) sobre breakpoints y convenciones móvil-first, sin duplicar toneladas de texto genérico.
- Comprobar rutas clave en ancho estrecho; ejecutar `npm run build` si se toca el front.

## Implementation notes (coder)
- **Storefront:** `Layout.jsx` envuelve la SPA en un drawer daisyUI con `id="drawer-nav"`, enlazado al botón hamburguesa de `Navbar` (antes el id no existía). Menú lateral móvil (`lg:hidden`): inicio, productos, solución personalizada, carrito.
- **Admin:** breadcrumbs con scroll horizontal; `min-w-0` en `drawer-content` y en `main` para reducir desbordamiento horizontal.
- **Carrito y checkout:** contenedores con `w-full min-w-0 max-w-*` para no forzar ancho en contextos flex.
- **Docs:** `docs/mobile-responsive.md` (breakpoints Tailwind, drawers, `min-w-0`, viewport).
- **Blade:** `viewport-fit=cover` en `welcome.blade.php`.

## Testing instructions
1. `npm install` (si hace falta) y `npm run build` — debe terminar sin errores.
2. `php artisan test` y `php artisan routes:smoke` — si el PHP local no tiene `pdo_sqlite`, los tests que usan SQLite en memoria fallan; repetir en un entorno con ese driver o con la BD del proyecto.
3. **Manual (viewport estrecho, por debajo de 1024px):**
   - Tienda: abrir/cerrar el menú hamburguesa; enlaces del drawer (inicio, productos, solución, carrito); cerrar con overlay.
   - Admin: en una ruta con breadcrumbs largos (p. ej. edición), comprobar scroll horizontal en breadcrumbs sin romper la cabecera.
   - `/cart` y `/checkout`: que el formulario y las tablas no provoquen scroll horizontal a nivel de página (scroll dentro de tablas si aplica).
4. Opcional: DevTools → vista móvil → revisar `/`, `/products`, `/checkout` con usuario autenticado.
