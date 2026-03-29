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
