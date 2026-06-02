# SEO: Open Graph / Twitter Card (WhatsApp) + feed Google Merchant Center

## Resumen

Mejorar cómo se ve la tienda al **compartir enlaces** (WhatsApp, Telegram, X/Twitter, Facebook, LinkedIn) y ofrecer un **feed de productos para Google Merchant Center** generado automáticamente por la app, sin mantenimiento manual recurrente del catálogo.

## Contexto actual

| Área | Estado |
|------|--------|
| **Sitemap XML** | `GET /sitemap.xml` — productos, categorías y páginas estáticas; caché 6 h (`SitemapController`). |
| **robots.txt** | Dinámico; apunta al sitemap. |
| **Meta tags en shell SPA** | `resources/views/welcome.blade.php` — OG/Twitter **genéricos** (nombre de tienda, sin imagen `og:image`). |
| **Rutas de producto** | `/products/{id}` — SPA React; crawlers y WhatsApp **no ejecutan JS**, ven siempre el mismo HTML de inicio. |
| **JSON-LD Product/Offer** | Reglas en `.cursor/rules/ecommerce-seo.mdc`; **aún no implementado** (fuera de alcance de este issue salvo dependencia mínima para GMC). |
| **Imágenes de producto** | `Product::images()`; fallback `/images/dummy.jpg` en modelo. |

**Problema principal al compartir:** un enlace a `/products/42` en WhatsApp muestra el título/descripción de la home, no del producto, y suele faltar `og:image` (WhatsApp lo exige con URL absoluta https).

## Objetivos

### 1. Open Graph + Twitter Card (+ WhatsApp)

Previews correctas al compartir URLs de:

- Home `/`
- Listado `/products`
- Ficha producto `/products/{id}`
- Ficha pack `/packs/{id}` (si aplica)
- Categoría `/categories/{id}/products` (título/descripción de categoría; imagen opcional)

**Requisitos técnicos (WhatsApp / Facebook / Telegram):**

- `og:title`, `og:description`, `og:url`, `og:type`, `og:site_name`
- **`og:image`** — URL **absoluta** `https://…` (no relativa); recomendado ≥ 300×300 px; ideal 1200×630 para `summary_large_image`
- `og:image:alt` cuando haya imagen de producto
- Twitter: `twitter:card` = `summary_large_image` cuando hay imagen; `twitter:title`, `twitter:description`, `twitter:image`
- `link rel="canonical"` coherente con la URL compartida
- i18n: preferir locale de la petición (`Accept-Language` / locale activo) para título y descripción cuando existan traducciones

**Enfoque recomendado (SPA Laravel + React):**

Los scrapers de WhatsApp/Google no renderizan React. Opciones válidas (elegir una en implementación):

| Enfoque | Pros | Contras |
|---------|------|---------|
| **A. Meta server-side por ruta** — En `routes/web.php`, resolver `/products/{id}` (y similares) en Blade **antes** del catch-all SPA: mismo bundle React, pero `<head>` con meta del producto. | Previews fiables; un solo despliegue. | Tocar routing; duplicar lógica de título/imagen respecto a React. |
| **B. Ruta “share” dedicada** — p. ej. `GET /share/products/{id}` solo HTML con meta + redirect/canonical a la SPA. | Aislado; fácil de probar con [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/). | URL compartida distinta de la URL canónica (canonical debe apuntar a `/products/{id}`). |
| **C. Detección de bot + prerender** | URL canónica única. | Más complejo; mantener lista de user-agents. |

**Preferencia del issue:** **A o B** — simple, predecible, sin servicio externo de prerender.

**React (complemento):** actualizar `document.title` y meta en cliente (`react-helmet-async` o utilidad ligera) para pestaña del navegador y scrapers que sí ejecuten JS; **no sustituye** el HTML inicial para WhatsApp.

**Imagen por defecto:** si el producto no tiene imagen, usar logo o imagen de marca en `public/images/` (URL absoluta).

### 2. Feed Google Merchant Center

Generar un feed **automático** desde la BD (productos activos, stock, precio, descuento, URL canónica, imagen, GTIN/MPN si existen en `code`).

**Entregable mínimo:**

- Endpoint público, p. ej. `GET /feeds/google-merchant.xml` (o `.tsv` según convención del equipo)
- Formato compatible con [Google Merchant Center product data spec](https://support.google.com/merchants/answer/7052112) (campos obligatorios: `id`, `title`, `description`, `link`, `image_link`, `availability`, `price`)
- Caché similar al sitemap (p. ej. 6 h) + invalidación al guardar producto/pack (evento `Product::saved` / observer, como sitemap si ya existe patrón)
- Referencia en `robots.txt` **opcional** (no indexar el feed como página web)
- Documentación en `docs/` con pasos **única vez** en Merchant Center

**Criterio “el dev no tiene que hacer nada” (post-despliegue):**

| Tarea | ¿Quién? |
|-------|---------|
| Implementar endpoint + caché + doc | Desarrollo (este issue) |
| Crear cuenta Merchant Center, verificar dominio, añadir URL del feed | Operador / negocio (**una vez**) |
| Re-subir CSV manualmente cada semana | **No** — el feed es URL viva; Google la refresca solo |
| Editar productos en admin | Equipo habitual — el feed refleja la BD |

Si en producción no hay cuenta GMC, el feed puede desplegarse igual; no rompe nada hasta que alguien registre la URL.

**Fuera de alcance de este issue (issues futuros):**

- JSON-LD completo en todas las fichas (issue separado recomendado)
- Google Ads / campañas
- Slugs en URL (`/products/{slug}`) — hoy es `{id}`; el feed debe usar la URL real desplegada
- Envío a Merchant Center vía Content API (solo feed URL es suficiente para MVP)

## Criterios de aceptación

- [ ] Compartir `https://{APP_URL}/products/{id}` en WhatsApp muestra **nombre del producto**, descripción acortada e **imagen del producto** (o fallback de marca).
- [ ] [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) y validador de tarjetas de X no muestran errores críticos en home + al menos una ficha producto.
- [ ] `og:url` y `canonical` coinciden con la URL pública del recurso.
- [ ] `GET /feeds/google-merchant.xml` responde 200, XML válido, solo productos `is_active`, precio con IVA según reglas actuales de tienda, `availability` acorde a `stock`.
- [ ] Feed documentado: qué URL registrar en GMC y que **no** requiere export manual periódico.
- [ ] Tests: al menos feature test del feed (conteo, campos obligatorios, producto inactivo excluido) y test de meta en una ruta producto (assert en HTML).
- [ ] i18n ca/es/en para strings de fallback en meta (descripción genérica de tienda).
- [ ] Sin secretos en el feed; solo datos ya públicos en el catálogo.

## Notas de implementación

- Reutilizar consultas de `SitemapController` / `Product::active()` para no duplicar reglas de visibilidad.
- Precio en feed: respetar `discount_percent` si el storefront ya lo aplica al precio mostrado.
- Imagen: URL absoluta con `url()` o `asset()` + dominio `APP_URL` en producción.
- Revisar `.cursor/rules/ecommerce-seo.mdc` y alinear canonical con sitemap existente.
- Tras cambios en `resources/js/` o Vite: `npm run build` en CI (ya cubierto por workflow).

## Verificación manual sugerida

1. Producto con imagen → pegar enlace en WhatsApp → preview correcto.
2. Producto sin imagen → preview con imagen fallback.
3. Abrir URL del feed en navegador → XML legible.
4. Validar una fila en [Merchant Center feed debugger](https://support.google.com/merchants/answer/7169157) (cuando exista cuenta).

## Labels sugeridos

`enhancement`, `seo`, `storefront`

## Referencias

- [WhatsApp link previews (Open Graph)](https://developers.facebook.com/docs/sharing/webmasters/)
- [Twitter Cards](https://developer.x.com/en/docs/twitter-for-websites/cards/overview/abouts-cards)
- [Google Merchant product data specification](https://support.google.com/merchants/answer/7052112)
- Código: `resources/views/welcome.blade.php`, `app/Http/Controllers/SitemapController.php`, `routes/web.php`
