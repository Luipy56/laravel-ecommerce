# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.164] - 2026-05-11

### Fixed
- SEO: added `<title>`, meta description, canonical, Open Graph, and Twitter Card tags to the Blade shell (`welcome.blade.php`).
- Accessibility: corrected invalid ARIA roles in drawer language selector (`role="group"` + `role="option"` to `role="radiogroup"` + `role="radio"`).
- Accessibility: added `aria-hidden` to decorative SVG chevron and `role="status"` to loading spinner on HomePage.
- HomePage sets `document.title` on mount so Lighthouse reads a proper page title.

## [0.1.160] - 2026-05-11

### Changed
- Product card price text, cart button, and pack badge/outline now use `--color-primary` (#fb5412) instead of `--color-primary-light` (#ffb300 yellow). Unified brand color across cards.
- `--color-primary-light` CSS variable updated to match `--color-primary` value; `.btn-primary-light` class now resolves to primary.

## [0.1.159] - 2026-05-11

### Changed
- SCSS visual merge: replaced Tailwind utility classes with SCSS BEM styles on **HomePage**, **ProductListPage**, and **ProductCard** (hero with gradient fallback, catalog sidebar, fluid product grid, price slider, category tags).
- Shared `_fluid.scss` partial with responsive `fluid()` SCSS function used by all three component SCSS files.
- Product card `.cart-btn` and `.product-card__pack-badge` reference `var(--color-primary-light)`.
- Homepage full-bleed background image (`home-bg.jpg`, fixed attachment).
- Section dividers (`border-top`) and consistent bold font-weight + letter-spacing on `.section-title`.
- Register and Custom Solution pages: privacy checkboxes vertically centered, fixed missing space before "Política de privacidad" link, removed `GdprNotice` blocks, privacy link scrolls to top.
- Agent version-bump rule clarified: bump per prompt-response turn, not per plan.

### Added
- `--color-primary-light: #ffb300` theme token in daisyUI `serralleria` theme; `.btn-primary-light` utility class.
- Hero banner image (`public/images/hero.jpg`) and homepage background image (`public/images/home-bg.jpg`).
- Editable number inputs on price range slider (replace static labels) with currency suffix.
- `shop.filters.clear`, `shop.filters.price_min`, `shop.filters.price_max`, `gdpr.accept_privacy_prefix` i18n keys (ca, es, en).

### Fixed
- Navbar-to-hero gap: `.home-page` negative margin extended to fully cancel `<main>` padding (top and bottom).
- Slider-btn (trending section arrow) scrolls to top when navigating to `/products`.
- Clear-filters button resets **all** filters (category, features, packs, price) and moved to top of sidebar.
- `shop_settings.value` column made nullable to prevent SQL integrity constraint error when saving empty textareas.
- Product card footer pinned to bottom via flex column layout with `margin-top: auto`.
- Catalog page top padding reduced for large screens.
- Hero image contained with `overflow: hidden`.

### Removed
- `/test` preview routes and `resources/js/test/` directory.

## [0.1.154] - 2026-05-09

### Fixed
- Sitemap XML no longer calls `toAtomString()` on a null `updated_at` (omit `lastmod` when missing) so `/sitemap.xml` does not return HTTP 500 after `routes:smoke`.

## [0.1.153] - 2026-05-09

### Added
- `POST /api/v1/cart/cancel-pending-checkout` (verified clients): clears deferred PSP checkout state when the buyer returns via cancel URL; `CheckoutPage` calls it on `?payment=ko`.

## [0.1.152] - 2026-05-09

### Changed
- Checkout with real Stripe or PayPal no longer converts the cart row to `kind=order` until payment succeeds (`PaymentCompletionService::markSucceeded`). Addresses and a pending payment are stored on the cart during PSP checkout; failed/canceled PSP flows revert the cart (clear addresses, remove incomplete payments, reset checkout-only shipping/installation pricing fields).
- `OrderPlacedPaymentPending` emails are not sent when checkout is deferred to the PSP; payment-confirmed emails still send after success.
- Stripe and PayPal return/cancel URLs use `/checkout` when the order is still a cart; `CheckoutPage` confirms Stripe sessions and captures PayPal returns without requiring `/orders/{id}` first.
- Cart API includes `payments` for logged-in users; cart line/installation/merge mutations return 422 while a PSP checkout payment is open (`shop.cart.psp_checkout_in_progress`).

## [0.1.149] - 1997-04-25

- `Olga`

## [0.1.149] - 2026-05-07

### Added
- `NullSafeEncrypted` custom Eloquent cast (`app/Casts/NullSafeEncrypted.php`): drop-in replacement for the built-in `'encrypted'` cast that returns `null` instead of throwing `DecryptException` when APP_KEY does not match; used for `Client.identification` and `PersonalizedSolution.problem_description/resolution/improvement_feedback`.
- `TracksDecryptionErrors` model trait (`app/Models/Concerns/TracksDecryptionErrors.php`): records which attributes failed decryption; controllers check `hasDecryptionErrors()` to include `_decryption_error: true` in their responses.
- `DecryptionWarningBanner` React component shown on admin pages when `_decryption_error` is true, with a clear message explaining the APP_KEY mismatch and how to fix it.

### Fixed
- `AdminOrderController::show()` now returns a full 200 response with `_decryption_error: true` instead of a 500 or 422; the order page loads with blank identification and shows the warning banner.
- `AdminClientController` index and show: `_decryption_error` flag propagated to response.
- `AdminPersonalizedSolutionController` index/show: manual per-field try/catch replaced with model-level `hasDecryptionErrors()`.
- `AdminReturnRequestController`: `client._decryption_error` flag propagated.
- All client-facing controllers (UserController, AuthController, PersonalizedSolutionController, PublicPersonalizedSolutionController) now silently get `null` instead of a 500 crash, because the model cast handles it.
- Warning banner added to: AdminOrderShowPage, AdminClientShowPage, AdminClientsPage, AdminPersonalizedSolutionShowPage, AdminPersonalizedSolutionsPage, AdminReturnRequestShowPage.
- Added `common.decryption_warning_title` and `common.decryption_warning_body` locale keys (ca/es/en).

## [0.1.148] - 2026-05-07

### Fixed
- `GET /api/v1/admin/orders/{id}`: catches `DecryptException` on `client.identification` instead of crashing with HTTP 500 "The payload is invalid." — order now loads successfully with the field blank when the encrypted value is unreadable.
- `GET /api/v1/admin/personalized-solutions`: per-row `DecryptException` handling so one corrupt encrypted row no longer crashes the entire list; affected rows carry `_decryption_error: true` with blank fields.
- `AdminPersonalizedSolutionController::show()`: individual catches for `problem_description`, `resolution`, `improvement_feedback`, and `client.identification` encrypted fields.
- Admin order show page now displays a localized, actionable error message instead of the raw cryptic "The payload is invalid." string.

## [0.1.147] - 2026-05-07

### Added

- **Orders — visual status tracker**: new shared `OrderStatusTracker` component renders a horizontal steps chain (daisyUI `steps`, primary/orange) showing the current position in the order workflow. Steps adapt when `installation_requested` is true (adds Instal·lació node). Appears at the top of `/orders/:id` for non-finalized orders, and at the top of `/orders` for the most recent active order.
- **Orders list — active-order banner**: `/orders` now shows a top card with the last open order's tracker and a direct link; if all orders are closed a friendly invite to browse new products is shown instead.

### Changed

- **Orders list (`/orders`)**: cards redesigned with bolder order number, coloured primary total, soft status badge, and full-row clickable link for better usability.
- **Order detail (`/orders/:id`)**: old text-based status history timeline replaced by the visual tracker; non-visual status alerts (awaiting quote, awaiting payment) remain below.

## [0.1.146] - 2026-05-07

### Added

- **Admin settings — Terms & Conditions block**: new collapse section in `/admin/settings` with three plain-text textareas (Catalan, Spanish, English) to edit the shop's terms and conditions. Content is stored in `shop_settings` under `terms_ca`, `terms_es`, `terms_en`.
- **Storefront `/terms` page**: new public page that renders the active language's terms text (fetched from `GET shop/public-settings`); falls back to the first non-empty language. Shows a neutral message when no content has been configured.
- **Footer link**: added "Termes i condicions / Términos y condiciones / Terms & Conditions" link in the legal footer section, alongside the existing privacy-policy link.

## [0.1.145] - 2026-05-07

### Fixed

- **Cookie banner**: replaced plain checkbox + parenthetical annotations ("always active", "optional") with cleaner toggle switches; removed annotation text.
- **Privacy policy**: wrapped each section in a card (`bg-base-100` + border) to match the visual style of other content pages.
- **Navbar user dropdown**: menu now closes automatically when a navigation link is selected (blurs focus so daisyUI CSS dropdown collapses).

## [0.1.144] - 2026-05-05

### Fixed

- **Seeders**: `UserSeeder` and `ClientContactSeeder` were using `DB::table()->insert()`, bypassing Eloquent's `encrypted` cast introduced in v0.1.142. Switched to `Client::create()` / `ClientContact::create()` so `identification`, `phone`, and `phone2` are stored as proper ciphertext; this resolves the `DecryptException: The payload is invalid` 500 error on all admin API endpoints during test runs.
- **Tests**: `ClientEmailVerificationTest` registration payload was missing the now-required `accept_privacy: true` field, causing a 422 rejection.
- **Tests**: `AdminPersonalizedSolutionResolutionPatchTest` was asserting the encrypted `resolution` column via `assertDatabaseHas` (raw DB query — always gets ciphertext). Replaced with a model-level assertion that decrypts via the cast.

## [0.1.142] - 2026-05-05

### Added

- **GDPR/LOPDGDD compliance implementation** (full plan):
  - `PrivacyPolicyPage.jsx` at `/privacy-policy` with all 10 sections (data controller, data inventory, purposes/legal basis, retention periods, recipients, security, subject rights, automated decisions, international transfers, policy changes); linked from Footer Legal nav in all 3 locales (ca/es/en).
  - `GdprNotice` component: compact informational banner (purpose, legal basis, privacy link) added above the submit button on `RegisterPage`, `CheckoutPage`, and `CustomSolutionPage`.
  - `FieldHint` component: daisyUI tooltip info icon on `identification`, `phone`, and address legend fields in `RegisterPage`, `ProfilePage`, and `CustomSolutionPage`.
  - Mandatory privacy-policy acceptance checkbox and optional marketing opt-in checkbox on `RegisterPage` and `CustomSolutionPage`; submit is disabled until privacy checkbox is ticked.
  - `client_consents` migration and `ClientConsent` model; `AuthController::register` records `privacy_policy` and optionally `marketing` consent (with IP, user agent, policy version) at registration.
  - `encrypted` cast on `Client.identification`, `ClientContact.phone`/`phone2`, `PersonalizedSolution.problem_description`/`resolution`/`improvement_feedback`; migrations updated to use `text()` columns to accommodate ciphertext.
  - `GET /api/v1/profile/export` — GDPR Art. 20 DSAR data portability endpoint returns full structured JSON of all client personal data.
  - `DELETE /api/v1/profile` — GDPR Art. 17 right-to-erasure endpoint anonymises the account while retaining financial records.
  - `GET /api/v1/profile/consents` — returns consent history for the authenticated client.
  - `GdprPurgeCommand` (`php artisan gdpr:purge [--dry-run]`) with per-table retention rules (sessions 30 d, personalized solutions 3 y, inactive clients 5 y, order addresses 7 y, payment refs 7 y); scheduled weekly Sunday 02:00 in `routes/console.php`.
  - `CookieConsentBanner` upgraded to v2: granular essential (always on) + analytics (opt-in) categories stored as JSON at `cookie_consent_v2`; includes privacy policy link.
  - `docs/gdpr-compliance.md`: full compliance reference (data inventory, retention matrix, consent recording, security measures, subject rights API, purge command, dev checklist).
  - `docs/database-security.md`: DB access control policy (least privilege, encrypted columns, backup, connection security, incident response).

## [0.1.141] - 2026-05-05

### Fixed

- PayPal refunds now issue a real monetary refund via the PayPal REST API (`POST /v2/payments/captures/{id}/refund`) instead of only updating the database. The capture ID is persisted to `payments.metadata['paypal_capture_id']` at the moment of payment capture. `ReturnRequestService::issueRefund()` now calls `PayPalClient::refundCapture()` for PayPal payments and stores the PayPal refund ID in `return_requests.gateway_refund_reference`, matching the behaviour of Stripe refunds. Payments captured before this fix lack a capture ID and will return a clear error directing the admin to refund manually via the PayPal dashboard.

## [0.1.140] - 2026-05-05

### Changed

- Storefront home (viewports below `sm`): featured products render in horizontal scroll strips, one strip per category (order follows the featured API); products without a category use `shop.featured_uncategorized`. Tablet and up keep the existing multi-column grid.

## [0.1.139] - 2026-05-05

### Changed

- Patch release (no functional changes).

## [0.1.138] - 2026-05-04

### Fixed

- `ProductScoutIndexingTest`: assert Scout `MakeSearchable` / `RemoveFromSearch` using `Queue::fake()` **after** the product row exists, then `searchable()` / `unsearchable()`; avoids `Bus::fake()` with Scout’s `dispatch()` + `PendingDispatch` (jobs never reach `Queue::fake()` when the bus is fully faked).

### Changed

- Transactional Blade mails: centered CTA action `<p>` wrappers (`text-align: center`) for order, installation, returns, and related templates (aligned with the transactional button layout used elsewhere).
- Storefront/admin React: `OrderDetailPage`, `OrdersPage`; RMA admin return request pages; review moderation show page; `ReviewsSection` and `StarRating` behaviour and layout.
- Locales `ca.json`, `es.json`, `en.json` (returns/reviews strings; complete missing English returns keys).
- `.cursor/rules/components.mdc`: shared component / `PageTitle` guidance.

### Test

## [0.1.137] - 2026-05-04

### Added

- English (`en.json`) translations for all 54 missing returns-feature keys: `shop.returns.*` (21 keys), `admin.nav.returns` + `admin.nav.alert_link_suffix_returns` (2 keys), and `admin.returns.*` (31 keys).

## [0.1.136] - 2026-05-04

### Added

- **ProductReviewSeeder:** 6 ressenyes de demo per a les dues pàgines de producte principals (cilStd, escEst, spEst, cilSeg). Mix d'estats: 3 aprovades (amb compra verificada), 2 pendents, 1 rebutjada. Els agregats `avg_rating` i `reviews_count` dels productes s'actualitzen automàticament via observer en executar el seeder.

## [0.1.135] - 2026-05-04

### Added
- `ReturnRequestSeeder`: seeds 4 demo return requests covering all RMA statuses (rejected, pending_review, approved, refunded). The refunded RMA also marks its order as `returned` and its payment as `refunded`.

## [0.1.134] - 2026-05-04

### Fixed

- **Correus transaccionals:** Els botons d’acció tipus «Veure la comanda» (enllaç estil CTA taronja) queden **centrats** al cos del missatge afegint `text-align: center` al paràgraf contenidor a les plantilles Blade que encara el tenien alineat a l’esquerra.

## [0.1.133] - 2026-05-04

### Added

- **Admin comanda en trànsit:** A la fitxa de comanda (`/admin/orders/:id`), si l’estat és «En trànsit», botó per enviar de nou el correu transaccional al client amb una previsió d’arribada (avui mateix / en pocs dies / desconegut). El modal confirma l’acció; si tria «desconegut», el correu usa text amable (p. ex. castellà «le llegará pronto»), no el literal de l’admin.
  - API: `POST /api/v1/admin/orders/{order}/notify-in-transit-mail` amb cos `delivery_eta`: `today` \| `few_days` \| `unknown` (throttle 30/min).
  - `OrderShippedMail` accepta un paràmetre opcional de previsió per al cos del correu; `lang/*/mail.php` amb claus `delivery_estimate_*`.

## [0.1.132] - 2026-05-04

### Added

- **RMA — Sistema de devolucions (Return Merchandise Authorization):** Flux complet de sol·licitud, aprovació i reemborsament de devolucions.
  - Nova taula `return_requests` (`order_id`, `client_id`, `payment_id`, `status`, `reason`, `admin_notes`, `refund_amount`, `refunded_at`, `gateway_refund_reference`).
  - Nou constant `Order::STATUS_RETURNED = 'returned'` i relació `Order::returnRequests()`.
  - Model `ReturnRequest` amb constants de status i scopes (`pendingReview`, `open`).
  - `ReturnRequestService` amb mètodes `create`, `approve`, `reject` i `issueRefund` (Stripe API + fallback manual per PayPal i altres gateways).
  - API REST client: `GET /api/v1/return-requests`, `POST /api/v1/orders/{order}/return-requests`.
  - API REST admin: `GET/GET/PUT /api/v1/admin/return-requests[/{rma}]`, `POST /api/v1/admin/return-requests/{rma}/refund`.
  - 4 mails transaccionals: `ReturnRequestReceivedAdminMail` (admin), `ReturnRequestApprovedMail`, `ReturnRequestRejectedMail`, `ReturnRequestRefundedMail` (client). Registrats via Events+Listeners.
  - `AdminNavAlertsController` ara retorna `returns_need_attention` per a sol·licituds `pending_review`.
  - Frontend client: botó "Sol·licitar devolució" + modal a `OrderDetailPage`, nova pàgina `/my-returns` (`ReturnRequestsPage`), accés des del menú del perfil.
  - Frontend admin: `AdminReturnRequestsPage` (`/admin/returns`) amb cerca, filtre d'estat i paginació; `AdminReturnRequestShowPage` (`/admin/returns/:id`) amb accions aprovar/rebutjar/emetre reemborsament. Afegit a la sidebar de `AdminLayout` (secció Operacions).
  - I18n: claus `shop.returns.*`, `admin.returns.*`, `admin.nav.returns` i `shop.status.returned` a `ca.json` i `es.json`. Claus de correu a `lang/ca/mail.php` i `lang/es/mail.php`.
  - `diagramZero.dbml` actualitzat amb taula `RETURN_REQUESTS` i nou estat `returned` a `order_status`.

## [0.1.131] - 2026-05-04

### Changed

- **Admin:** List toolbar filters (search, type/status/active/period/category, etc.) are persisted in `localStorage` under the key `le-admin-list-filters` so choices survive navigation away from the page and full browser reloads. Applies to orders, personalized solutions, products, clients, categories, FAQs, packs, admins, variant groups, reviews, and both sections of the features page.

## [0.1.130] - 2026-05-04

### Added

- **Ressenyes i valoracions de productes:** Els clients que han comprat un producte poden deixar una valoració (1-5 estrelles + comentari opcional). Les ressenyes queden en estat `pending` fins que un administrador les aprova o rebutja. Només les ressenyes aprovades es mostren a la fitxa de producte.
  - `product_reviews` table: `product_id`, `client_id`, `order_id` (compra verificada), `rating`, `comment`, `status` (pending/approved/rejected), `admin_note`.
  - Columnes `avg_rating` i `reviews_count` denormalitzades a la taula `products`, actualitzades automàticament via `ProductReviewObserver`.
  - API pública `GET /api/v1/products/{id}/reviews` (llista paginada + agregat de distribució).
  - API de client autenticat `POST/GET .../reviews` per enviar i consultar la pròpia ressenya (amb verificació de compra completada).
  - API d'admin `GET/PATCH/DELETE /api/v1/admin/reviews/{id}` per moderar.
  - **Storefront:** `ReviewsSection` + `ReviewForm` afegits a la fitxa de producte; formulari condicionat a compra verificada; estat de ressenya pròpia visible.
  - **Admin:** `AdminReviewsPage` (cua de moderació, filtre per estat, cerca), `AdminReviewShowPage` (detall + aprovar/rebutjar/eliminar).
  - Alerta de punt taronja al menú lateral de l'admin quan hi ha ressenyes pendents.
  - Component compartit `StarRating.jsx` (daisyUI `mask-star-2`).
  - i18n afegit per `admin.reviews.*` i `shop.reviews.*` en ca/es/en.

## [0.1.129] - 2026-05-04

### Added

- **Sitemap XML dinámico:** `GET /sitemap.xml` generado por `SitemapController` con todas las páginas estáticas (home, productos, FAQ, juegos) más URLs dinámicas para cada categoría activa (`/categories/{id}/products`) y cada producto activo (`/products/{id}`). Resultado cacheado 6 horas; cache invalidada automáticamente cuando se guarda o elimina un producto o categoría.
- **robots.txt dinámico:** La ruta `/robots.txt` pasa por Laravel y emite la directiva `Sitemap:` con la URL absoluta correcta según `APP_URL`, para que cualquier entorno genere el valor adecuado. Se eliminó el fichero estático `public/robots.txt`.
- **Nginx:** `docker/nginx/default.conf` actualizado para enrutar `/robots.txt` a través de PHP (`try_files $uri /index.php?$query_string`) en lugar de servirlo solo como fichero estático.

## [0.1.128] - 2026-05-04

### Added

- **Buscaminas:** Juego de Buscaminas (fthielke/html-minesweeper, MIT) añadido en `public/games/buscaminas/`. Cadenas de UI traducidas al español ("Nueva partida", "Minas", "Tiempo").
- **Wordle ES:** Wordle en castellano (danielrouco/wordle) añadido en `public/games/wordle-es/`. Dependencia de Google Fonts eliminada; botón de reinicio sustituido por carácter unicode.
- **Wordle CA:** Wordle en català (patobottos/catalan-wordle-js) añadido en `public/games/wordle-ca/`. Instrucciones reducidas para mejorar el ajuste en el iframe.
- **Config compartido de juegos:** `resources/js/config/games.js` extrae el array `GAMES` para ser importado desde `GamesPage`, `NotFoundPage` y `ErrorPage`, eliminando la duplicación.
- **i18n:** Claves `games.game_buscaminas_title`, `games.game_wordle_es_title`, `games.game_wordle_ca_title` añadidas en `ca.json`, `es.json`, `en.json`.

## [0.1.116] - 2026-05-04

### Added

- **Minijuegos:** Se añaden 3 minijuegos de código abierto (2048, Dinosaurio corredor, Tetris) accesibles desde las páginas de error 404 y 500, y desde el menú de perfil del usuario. Los juegos se alojan localmente en `public/games/` sin dependencias externas.
- **Componente `MiniGameEmbed`:** iframe reutilizable con estado de carga y fallback de error ("Juego no disponible") para cuando el archivo no se pueda cargar.
- **Página `/games`:** Nueva página dedicada con selector de juego y vista embebida.
- **Página `ErrorPage` y `ErrorBoundary`:** Captura errores de React no controlados y muestra la página de error con acceso a juegos.
- **Vistas Blade de error:** `resources/views/errors/500.blade.php` y `404.blade.php` para errores PHP previos a la carga del SPA, con enlace a `/games`.
- **i18n:** Claves `games.*` y `errors.server_error_*` añadidas en `ca.json`, `es.json`, `en.json`.

## [0.1.115] - 2026-05-04

### Fixed

- **Email de confirmación de pago enviado dos veces con Stripe:** `PaymentCompletionService::markSucceeded` usaba un `SELECT` normal dentro de la transacción, lo que permitía una condición de carrera entre el webhook `checkout.session.completed` de Stripe y el endpoint `/payments/stripe/checkout/confirm` llamado automáticamente al volver del checkout. Ambos leían el pago como pendiente antes de que el otro confirmara y los dos despachaban `OrderPaymentSucceeded`. Corregido usando `lockForUpdate()` (SELECT FOR UPDATE) al releer el registro, garantizando que solo uno de los dos caminos procese la transición y envíe el email.

## [0.1.114] - 2026-05-04

### Fixed

- **Checkout: flujo de pago PayPal inline no abandonaba la página de checkout.** Tras pulsar "Finalizar pedido" con PayPal como método de pago, el cliente ya no es redirigido a `/orders/:id`; los botones de pago de PayPal aparecen directamente en la misma página de checkout con scroll automático al panel de pago, permitiendo completar el pago sin abandonar el flujo.
- **Checkout: pantalla "carrito vacío" tras crear pedido con pago pendiente.** Cuando el carrito se vaciaba al crear el pedido (antes de que el cliente completara el pago), la página mostraba "carrito vacío" en lugar del panel de pago inline. Ahora el guard de carrito vacío se ignora mientras haya un pago activo en proceso.

### Changed

- **Cart (móvil): nombre de producto truncado.** En pantallas pequeñas el nombre del producto en la tabla del carrito se trunca con elipsis (`…`) para evitar filas muy altas, y los atributos (marca, color, etc.) se ocultan en móvil. En escritorio se muestra el nombre completo con todos los atributos sin cambios.

## [0.1.113] - 2026-05-04

### Fixed

- **`lang/ca/mail.php` PHP syntax error:** Catalan apostrophes in array string values were unescaped inside single-quoted PHP strings, causing a `ViewException` (`syntax error, unexpected identifier "enllaç"`) whenever any email view was rendered with the `ca` locale.
- **`priceRange` API endpoint (HTTP 500):** `GET /api/v1/products/price-range` threw a `ValueError` from `min([])` / `max([])` when the database contained no active products or packs (empty test DB). Now returns `{min: 0, max: 0}` when no data is available.
- **Admin pending-payment email not sent:** `SendOrderPaymentPendingAdminEmail` listener was accidentally de-registered from the `OrderPlacedPaymentPending` event in a previous commit. Re-registered so admin receives the notification when an order is placed with a pending payment.
- **`CustomerTransactionalEmailTest` assertion mismatch:** Test expected `"Resumen de tu solicitud"` (informal) but the Spanish translation was updated to the formal `"Resumen de su solicitud"` in a later commit; test now matches the current translation.

## [0.1.112] - 2026-05-04

### Changed

- **Albarán (delivery note) PDF/HTML view (`resources/views/pdf/delivery_note.blade.php`):** Reworked as a proper proof-of-delivery document. Now uses an `ALB-{year}-{order_id}` document number, drops the price / unit-price / line-total / shipping / installation / total columns (delivery notes have no fiscal value), shows a quantity-only line table with a "Total units" footer, and adds a recipient signature block (signature line + date) plus a free-form delivery-remarks box. A prominent "no fiscal validity" banner explains the document's purpose and points at the separate invoice for VAT.
- **Factura (invoice) PDF/HTML view (`resources/views/pdf/invoice.blade.php`):** Reworked as a Spanish legal invoice. Adds an `FAC-{year}-{order_id}` invoice number, the issue date (paid date when available), an "ISSUER" panel with brand name + tax ID (NIF/CIF) + fiscal address, a "BILL TO" panel with the client name, identification (DNI / NIE / CIF) and email/phone, an IVA breakdown row (taxable base, VAT amount, total with VAT) computed from a configurable `INVOICE_VAT_RATE_PERCENT` (default 21 %), a payment block (method, paid-at, gateway reference) shown only when a successful payment exists, a "PAID" stamp in the header, and a legal-validity notice for accounting / VAT-deduction purposes.

### Added

- **Issuer fiscal config** in `config/mail.php` under `mail.brand`: `tax_id` (NIF/CIF/VAT), `fiscal_address` (multiline registered address) and `vat_rate_percent` (default 21). Wired to env vars `MAIL_BRAND_TAX_ID`, `MAIL_BRAND_FISCAL_ADDRESS`, `INVOICE_VAT_RATE_PERCENT` and documented in `.env.example`. Stored product prices are treated as VAT-inclusive (Spanish B2C standard); the invoice deducts the base from the grand total using the configured rate.
- **i18n keys for invoice + delivery note** in `lang/ca.json` / `lang/es.json` / `lang/en.json` (`shop.invoice_number_label`, `shop.invoice_issue_date`, `shop.invoice_tax_id`, `shop.invoice_fiscal_address`, `shop.invoice_taxable_base`, `shop.invoice_vat_label`, `shop.invoice_total_with_vat`, `shop.invoice_vat_included_note`, `shop.invoice_payment_*`, `shop.invoice_legal_notice`, `shop.delivery_note_title`, `shop.delivery_note_subtitle`, `shop.delivery_note_no_fiscal_value`, `shop.delivery_note_signature_label`, `shop.delivery_note_signature_hint`, `shop.delivery_note_total_units`, `shop.delivery_note_carrier_notes`, `shop.delivery_note_footer`, `shop.delivery_note_received_on`, `shop.delivery_note_ship_to`). The previously broken `__('shop.delivery_note_*')` calls (which fell through to the literal key string) now resolve in all three locales.

### Fixed

- **N+1 on invoice render:** `OrderController@invoice` now eager-loads the `payments` relation alongside lines/addresses/client; the new invoice template reads it for the "PAID" stamp and the payment block.

## [0.1.108] - 2026-05-04

### Removed

- **i18n:** Dropped unused `checkout.payment.bizum` (ca / es / en). Checkout only exposes `card` and `paypal` methods in the UI; Bizum is included under the Stripe card flow. **`admin.orders.payment_bizum` is kept** — it is still resolved when an order payment row has `payment_method` `bizum`.

## [0.1.105] - 2026-05-04

### Fixed

- **Storefront orders list:** Empty state no longer reused the cart message; it now shows `shop.orders.empty` (orders-specific copy in ca / es / en).

## [0.1.104] - 2026-05-04

### Added

- **Admin shop settings:** Short one-line subtitles on collapse headers for Home, Custom solutions, and Admin list columns; “List defaults” and “Closed prices” keep their existing help lines.

## [0.1.103] - 2026-05-04

### Changed

- **Admin shop settings:** Removed the long collapse subtitle under “Home · automatic highlights” / “Inicio · destacados automáticos”; in-section copy (limits hint, field labels) is unchanged.

## [0.1.102] - 2026-05-04

### Changed

- **Admin shop settings:** Removed the long subtitle under “Admin list columns” / “Columnas de listas del admin”; per-table hints inside the section remain.

## [0.1.99] - 2026-05-03

### Removed

- **Offline payments (bank transfer / Bizum manual):** Complete removal of all offline payment infrastructure — `PaymentOfflineInstructions` support class, `Payment::METHOD_BANK_TRANSFER` / `METHOD_BIZUM_MANUAL` / `GATEWAY_MANUAL` / `isOfflineCheckoutMethod()`, five `ShopSetting` keys and defaults (`bank_transfer_iban`, `bank_transfer_beneficiary`, `bank_transfer_reference_hint`, `bizum_manual_phone`, `bizum_manual_instructions`), admin settings UI section, `recordManualSettlement` admin API endpoint and route, `OfflinePaymentInstructionsBlock` React component, all related checkout/order-detail UI branches, and offline locale keys in JS and PHP i18n files. Stripe Checkout Bizum (online, via `card` method) is unaffected.

## [0.1.97] - 2026-05-03

### Added

- **Storefront — price range filter:** New dual-handle range slider in the product list sidebar (below "Solo packs") lets shoppers set a min/max price. The slider bounds are fetched from a new `GET /api/v1/products/price-range` endpoint. When the range matches the global min/max no filter is sent; a "Cualquier precio" link resets it. The `price_min` / `price_max` params are persisted in the URL and forwarded to both product and pack queries.

## [0.1.96] - 2026-05-03

### Fixed

- **Admin nav alert — orders:** Removed an `orWhere` clause from the `orders_need_attention` query that incorrectly triggered the sidebar dot for orders with `installation_requested=true` and `installation_status=pending` regardless of the order's main status (e.g. already-sent orders). The dot now only activates for orders whose status is `pending`, `awaiting_payment`, `awaiting_installation_price`, or `installation_pending`.

## [0.1.95] - 2026-05-03

### Fixed

- **Locale (es/ca):** Period filter option "all time" corrected to "Todos los tiempos" (es) and "Tots els temps" (ca).

## [0.1.94] - 2026-05-03

### Added

- **Admin Orders & Personalized Solutions — period filter:** Both list pages now include a time-range select (Last week / Last month / Last year / All time). The backend `AdminOrderController` and `AdminPersonalizedSolutionController` accept a `period` query param and apply a `created_at >=` date constraint accordingly.
- **Admin Settings — default period:** New "List defaults" section in `/admin/settings` lets admins choose which period is pre-selected when the Orders and Personalized Solutions pages are first opened. Persisted in `shop_settings` under key `admin_list_default_period`; default is "last week".

## [0.1.93] - 2026-05-03

### Changed

- **Admin lists (all):** Replaced prev/next pagination buttons with infinite scroll (IntersectionObserver sentinel) across all 11 admin list pages: Products, Categories, Features (both sub-lists), Variant Groups, Packs, Orders, Clients, Personalized Solutions, Admins, FAQs, and Data Explorer. New items are appended automatically as the user scrolls to the bottom; a small spinner appears while loading more.

## [0.1.89] - 2026-05-03

### Changed

- **Client portal (personalized solution):** Removed "Ronda de mejoras: X" from the client view — it's irrelevant information for the client.
- **Client portal (personalized solution):** Improvement request textarea now pre-fills with the client's last sent message on page load and after a successful submit, so the previous text is not lost.
- **Admin personalized solution show:** The client's improvement feedback text is now visually highlighted with a left warning border and tinted background so it stands out as the key text to read.
- **Locales (ca/es):** Renamed "Última petició/petición de millores/mejoras" → "... del client/cliente" to clarify it refers to the client's message.

## [0.1.88] - 2026-05-03

### Fixed

- **Personalized solution improvement request email (500):** Laravel reserves the `$message` variable in Blade email views (injected as `Illuminate\Mail\Message`). The mailable was passing `'message' => $clientMessage` in `with: [...]`, which Laravel silently overwrote with its own mail object, causing `htmlspecialchars()` to receive an object and throw a fatal 500. Renamed the data key to `clientMessage` in the mailable and updated the blade template accordingly.

## [0.1.87] - 2026-05-03

### Changed

- **Cursor rules (`commit-changelog-version.mdc`, `agent-task-version-bump.mdc`, `app-version-cadence.mdc`):** Ship-ready changelog bullets must use **versioned sections** **`## [X.Y.Z] - YYYY-MM-DD`** aligned with root **`package.json`** after each bump; do not accumulate long lists under **`[Unreleased]`** so operators and Admin About always tie changes to a semver.

## [0.1.86] - 2026-05-03

### Fixed

- **PayPal "normativas internacionales" on retry:** `invoice_id` was built from the shop order ID only (`ORD-{order_id}`), so every failed attempt reused the same value. PayPal's compliance engine blocks duplicate `invoice_id` across multiple orders and returns a compliance-violation decline. Fixed by including the payment ID: `ORD-{order_id}-PAY-{payment_id}`, making each attempt unique.

- **Stripe not loading:** `.env` had `STRIPE_PUBLISHABLE_KEY`/`STRIPE_SECRET_KEY` but `config/services.php` reads `STRIPE_KEY`/`STRIPE_SECRET`; renamed the variables so `StripeCredentials::areConfigured()` returns `true` and Stripe Checkout sessions can be created.
- **Stripe excluded from checkout:** `PAYMENTS_CHECKOUT_METHODS` was set to `paypal` only; changed to `card,paypal` so the Stripe (card) option appears in the payment selector.
- **PayPal inline buttons never rendered:** `CheckoutPage.jsx` and `OrderDetailPage.jsx` checked `c.approval_url` before `c.client_id && c.paypal_order_id && c.payment_id`; since PayPal always returns an `approve` link, the inline buttons branch was dead code and `PayPalInlineButtons` was never mounted. Swapped the branch order so the inline SDK flow is always preferred.
- **PayPal capture missing after popup redirect:** Added capture call in the `payment=paypal_return` handler in `OrderDetailPage.jsx`; reads the `token` query param (PayPal order ID injected by PayPal on redirect), finds the matching pending payment, and calls `POST payments/paypal/capture` to complete the payment server-side.

### Added

- **Storefront favorites:** Authenticated clients can favorite products and packs via **`POST /api/v1/favorites/toggle`**, list IDs with **`GET /api/v1/favorites/ids`**, list items with **`GET /api/v1/favorites`**, and remove a line with **`DELETE /api/v1/favorites/lines/{orderLine}`** (favorites stored as a dedicated **`orders.kind = like`** basket). React: **`FavoritesPage`**, **`FavoriteToggle`**, **`useFavoriteIdsQuery`**, nav link from **`Layout`**.

- **Payments (PSP + offline):** Configurable **`bank_transfer`** and **`bizum_manual`** checkout methods (whitelist via **`PAYMENTS_CHECKOUT_METHODS`** / `config/payments.php`); shop settings for IBAN / Bizum instructions; checkout and **`POST orders/{id}/pay`** return **`payment_checkout.gateway`** `manual` plus **`payment_instructions`** without calling Stripe/PayPal. **`POST /api/v1/payments/stripe/checkout/confirm`** (authenticated, verified client) completes a paid Stripe Checkout session server-side (shared logic with the Stripe webhook). **`POST /api/v1/admin/orders/{order}/payments/{payment}/record-manual-settlement`** marks an offline pending payment succeeded and advances the order. Storefront: checkout/order pay UI, order detail Stripe return handling with **`session_id`**, offline instructions panel; admin shop settings and order “record manual settlement” action. API: **`GET payments/config`** and **`GET orders/{id}`** expose offline method flags and instruction hints.

- **Admin · shop configuration:** Collapsible sections (daisyUI **`collapse`**); **default flat shipping (EUR)** and **automatic installation pricing** (quote threshold + editable tiers) persisted in **`shop_settings`** (`shipping_flat_eur`, `installation_auto_pricing`); cart, checkout, order totals, PDFs, and admin order updates use these values. Placeholder block for **shipping by postal code** (not implemented). **`App\Support\InstallationAutoPricing`**; **`Order::automaticInstallationFeeFromMerchandiseSubtotal`** accepts optional merged settings for unit tests without a database.

- **Docker:** **`docker-compose.yml`** for local development (PostgreSQL, Nginx, PHP 8.2 FPM, Vite, queue worker, named volumes for `vendor` / `node_modules`), **`docker-compose.prod.yml`** for production builds, multi-stage **`docker/php/Dockerfile`**, **`docker/nginx/default.conf`**, **`docker/nginx/Dockerfile.prod`**, **`.dockerignore`**, and **`docker/php/docker-entrypoint.sh`**. **`config/trustedproxy.php`** wires **`TRUSTED_PROXIES`** into Laravel’s **`TrustProxies`** middleware. **`vite.config.js`** supports **`DOCKER=1`** for HMR behind the dev server.

### Changed

- **Order status badges (admin + storefront):** Status chips use **`badge-outline`** with semantic colors (warning / success / info / neutral) for stronger hue than soft fills while keeping an open, table-friendly look (tinted text and border, not a solid block). **`awaiting_installation_price`** stays on the warning hue (not info blue).

- **Product list sidebar:** **Packs only** toggle sits **below** the **Categories** block (same **`space-y-6`** spacing as before).

- **Storefront mobile drawer · account rows:** **Perfil**, **Comandes**, **Compres**, and guest **login** use the same **`drawerNavClass`** / **`drawerIconClass`** treatment as the main links (**`NavLink`** + **`IconUser`**, **`IconClipboardList`**, **`IconPackage`**, **`IconLogIn`**).

- **Storefront mobile drawer:** **Perfil** / **Comandes** / **Compres** (session) and guest **login** sit directly under the main nav list, separated by a thin **`hr`**; footer strip keeps language + brand only.

- **Storefront mobile drawer · locale panel:** Removed the redundant **close (X)** row inside the language card; the trigger, outside tap, and **Escape** remain sufficient.

- **Storefront mobile drawer (`Layout.jsx`):** Hamburger panel restyled (brand line, header close, icon + active state on primary nav, footer with brand/tagline, account shortcuts, language control). Shared **`STOREFRONT_LANGUAGE_OPTIONS`**; daisyUI **`dropdown-close`** when the locale menu is shut so the panel does not stay open on **`:focus-within`**; blur focused controls inside the widget; outside **`pointerdown`** (capture) to dismiss.
- **Navbar:** Below **`lg`**, hide the locale control and guest **login** (available in the mobile drawer). Navbar locale dropdown uses the same **`dropdown-open` / `dropdown-close`** pattern as the drawer; sync **`locale`** state when **`i18n.language`** changes.
- **Navbar (logged-in user):** Desktop account menu uses a **card** panel (gradient header, avatar chip on trigger, **`IconChevronDown`**) and the same icon set as the mobile drawer (**`IconUser`**, **`IconClipboardList`**, **`IconHeart`**, **`IconPackage`**, **`IconLogOut`**) on rounded hover rows; logout separated with error styling.
- **Icons (`icons/index.jsx`):** **`IconHome`**, **`IconGrid`**, **`IconSparkles`**, **`IconHelpCircle`** for drawer links; **`IconLogOut`** for account sign-out.

### Added

- **`resources/js/lib/storefrontLanguageOptions.js`:** Single source for storefront locale labels (ca / es / en) used by **Navbar** and **Layout**.

### Fixed

- **Transactional email sender name:** When **`APP_NAME`** was unset or still the framework default, **`MAIL_FROM_NAME="${APP_NAME}"`** (as in **`.env.example`**) resolved to **“Laravel”** in clients’ inboxes. **`config/app.php`** now defaults **`APP_NAME`** to **Serralleria Solidària**, matching **`MAIL_FROM_NAME`** / branding.

- **Icons (`IconUser`, `IconHelpCircle`):** SVG **`d`** arc flags were glued to coordinates (`011-7.5`, `0118`), which broke React’s DOM parser and distorted the profile icon; paths now use explicit separators (`0 1 1 -7.5`, `0 0 1 18`, etc.).

- **Password reset API:** **`POST /api/v1/reset-password`** no longer reads **`password_confirmation`** from **`validated()`** (Laravel omits it even when **`password`** uses **`confirmed`**), which caused an undefined index and HTTP **500**. The confirmation value is taken from the request body.

- **Admin · index column settings:** Sortable column rows use **`items-center`** so the drag handle, checkbox, and label align vertically in **`AdminIndexColumnsFieldset`**.

- **Client verification email:** Laravel routes notification mail to an `email` attribute; storefront clients only have `login_email`, so verification (and password-reset) messages were skipped. **`Client::routeNotificationForMail()`** now returns `login_email`. Registration and **resend verification** set **`MailLocale`** from **`Accept-Language`** like other transactional mail.

### Added

- **Cursor rules:** **`agent-verification-opt-in.mdc`** — unless the user explicitly requests verification, agents **skip** running **`php artisan test`**, **`routes:smoke`**, **`npm run build`**, **`migrate:fresh --seed`** for QA gates; **mandatory** root **`package.json`** patch bump before **`git push`** when tracked files changed (overrides default **testing-verification** / smoke execution). **`AGENTS.md`** and **`docs/agent-cursor-rules.md`** updated.

- **Cursor rules:** **`agent-task-version-bump.mdc`** — mandatory root **`package.json`** patch bump after **each agent-completed task** that commits; **`app-version-cadence.mdc`** / **`commit-changelog-version.mdc`** aligned; **`docs/agent-cursor-rules.md`** inventory updated.

- **Storefront auth:** `clients.email_verified_at` and `remember_token`; `Client` implements email verification and password reset; JSON routes **forgot-password**, **reset-password**, **email/resend**, signed **email/verify/{id}/{hash}**; middleware **`client.verified`** on sensitive storefront APIs; **`GET reports/summary`** with **`auth.client_or_admin`** (shop metrics for admins, scoped metrics for verified clients).
- **FAQ:** `faqs` table, public **`GET /faqs`**, admin **`apiResource`**, storefront FAQ page and admin CRUD (ca / es / en).
- **Delivery note (albarán):** **`GET /orders/{order}/delivery-note`** (same rules as invoice), Blade **`pdf/delivery_note`**, links on order detail and orders list.

- **Storefront / admin navigation:** React routes **`/faq`**, **`/forgot-password`**, **`/reset-password`**, **`/verify-email`**, admin **`/admin/faqs`** (and new/edit); **FAQ** and **forgot password** links in navbar, footer, and login; **register** hint about the verification screen; **login** success line when opening **`/login?verified=1`** after email verification.

### Changed

- **Production ops:** Base URL in server **`.env`** corrected (production deployment).

- **Admin · About (`/admin/about`):** Laia Martín is linked to **GitHub** (**ViperBite03**), same pattern as Yoel Berjaga’s site link.

- **Transactional email addresses:** Registration, login, password reset, and personalized-solution **`POST`** validate **`login_email` / `email`** with **RFC + DNS (MX-capable domain)** via **`ValidationRules::emailDns()`**; clearer validation messages in **ca / es / en**. Does not prove a mailbox exists on third-party hosts. Feature tests use **`@ietf.org`** sample addresses because **`example.com`** / **`example.org`** publish null MX (RFC 7505).

- **Cart · quantity column:** Quantity **`input`** uses **`w-16`** like extra-keys (was **`w-20`**).

- **Cart line · extra keys column:** The **input** is vertically centered in the row; the **€/u** line sits **`absolute`** under the input so it does not shift the centering anchor (inner wrapper height = input only).

- **Admin · shop settings (Inici):** **General** only has **max manual** featured; **max tendència** sits in **Stock baix** / **Sobrestock** blocks without redundant «(stock bajo)» / «(sobrestock)» in labels. New i18n **`admin.settings.section_general`**.

- **Login page:** Primary **Iniciar sesión** / **Log in** submit uses the **brand gradient** (**`from-primary` → `to-secondary`**) and shadow like **verify-email** and the scroll FAB.

- **Auth notification HTML (verify + reset):** **Verify email** and **reset password** mails use the same **branded** **`emails.layouts.transactional`** layout as other shop mail (logo, orange gradient, CTA), with copy in **`lang/*/mail.php`**; **`App\Support\FrontendPasswordResetUrl`** builds the SPA reset link.

- **Forgot password:** **`auth.forgot_sent`** (ca / es / en) is now a short confirmation that a **recovery email was sent**, instead of the conditional “if the email exists…” wording.

- **Register (`RegisterPage`):** Removed the global mandatory-field legend line; required fields remain marked with **`*`** on labels.

- **Verify email page:** Removed the **Home / Inicio** ghost button from **`EmailVerifyPage`**; navigation to **`next`** after verification is unchanged. Primary **resend** control is **centered** in the card.

- **Storefront navbar · language menu:** Replaced the plain **dropdown** with a **card** panel (title, **close** control, **gradient** highlight for the active locale, checkmark), **click-outside** and **Escape** to dismiss; trigger shows **CA/ES/EN** with clearer styling. New **`IconX`**, i18n **`common.language`**.

- **Email verification UX:** Removed **`EmailVerificationBanner`**. Dedicated **`/verify-email`** page (copy, **resend** with cooldown, **poll** **`GET user`**, **`navigate`** to **`next`** when verified); **register** → **`/verify-email?next=/`**, unverified **login** → **`/verify-email?next=…`**; **`AuthContext`** **`login`** / **`register`** now return **`user`** for redirects.

- **Storefront / admin forms:** On failed submit (Zod or API error), the UI scrolls so feedback is visible: **`scrollWindowToTopOnFormError()`** for full-page and drawer forms, **`scrollOpenModalBoxToTop()`** for daisyUI modals (profile address/contact). Shared helpers in **`resources/js/lib/formScroll.js`**; pattern documented in **`.cursor/rules/react-use.mdc`**.

- **Admin · shop settings · index columns:** Visible columns and **column order** are configurable per table (drag-and-drop in settings); list pages render columns in **`orderedVisibleColumnIds`** order. **`AdminIndexColumns::normalize()`** preserves stored order.

- **Scroll to top:** FAB uses **brand gradient** (**`from-primary` → `to-secondary`**), subtle **inset ring**, stronger shadow on hover, and **motion-safe** press feedback (still **`btn-circle`**).

- **App version in SPA:** **`welcome.blade.php`** sets **`window.__LARAVEL_APP_VERSION__`** from **`config('app.version')`**; **`resources/js/config/version.js`** prefers it over Vite **`define`** so admin sidebar / footer match **`package.json`** without restarting the dev server.

- **Admin layout:** Sidebar **`menu`**: first block = Panel + sorted **catalog / ops** links; second block = **Settings**, **Data explorer**, **FAQ**, **About**, **Back to shop** (horizontal rule between).

- **Custom solution (`/custom-solution`):** With an active session, **email**, **phone**, and **address** fields pre-fill from **`GET /api/v1/user`** (primary contact + first saved address); empty fields only so late auth load does not overwrite user typing.

- **API + storefront locale:** `Accept-Language` (ca / es / en) is sent on all **`api`** requests and middleware **`SetApiLocaleFromAcceptLanguage`** sets the app locale so **validation** messages (e.g. postal **regex**) match the UI language. Custom **`lang/*/validation.php`** entries for postal fields; **`CustomSolutionPage`** shows API errors via **`messageFromApiValidationError`** and maps **`fieldErrorsFromApiValidation`** onto inputs (red borders / hints).

- **Admin / personalized solution resolution modal:** Title is a single word (**`resolution_modal_title`**); status field uses **`select-md`**, **`text-base`**, **`max-w-md`**, and **`gap-8`** before the textarea; textarea label **`resolution_modal_text_label`** (*Texto de la resolución* / …).

- **Admin · shop settings:** Toggle and checkbox labels use **`w-full min-w-0`**, **`items-start`**, **`shrink-0`** on controls, and **`min-w-0 flex-1`** on **`label-text`** so long strings wrap on narrow viewports instead of overflowing the card.

- **Admin · About · team:** Simplified to two lines (Yoel Berjaga as link to **ldeluipy.es**, Laia Martín); removed intro and bios; i18n names updated in **ca** / **es** / **en**.

## [0.1.33] - 2026-04-30

### Changed

- **Postal codes (digits only):** Shared **`resources/js/lib/postalInput.js`** (`sanitizePostalCodeDigits`, `coercePostalCodeFieldValue`) used on **CustomSolution**, **Register**, **Checkout**, **Profile** addresses, **client personalized-solution** portal, and **admin personalized-solution edit**. Inputs use **`inputMode="numeric"`**, **`pattern="[0-9]*"`**, and **`autoComplete="postal-code"`** where appropriate.
- **Zod:** **`postalCodeRequired`** / **`postalCodeLoose`** and checkout’s optional installation postal require **1–20 digits**; new i18n **`validation.postal_digits`** (ca / es / en).
- **API:** Laravel validation uses **`regex:/^\d{1,20}$/`** (or **`^\d{0,20}$`** for nullable installation postal when installation is off) on register, personalized solutions, profile addresses, and order checkout.

## [0.1.30] - 2026-04-30

### Added

- **Admin · About (`/admin/about`):** Sidebar entry **About / Quant a / Acerca de** with application version (**`APP_VERSION`**), development team (Luipy56, Laia Martín de la Fuente), technical stack summary (ca / es / en), and the **full repository changelog** loaded dynamically from **`CHANGELOG.md`** via **`GET /api/v1/admin/changelog`** (**`AdminAboutController`**). Markdown rendered in the SPA with **react-markdown**.

### Changed

- **Admin order detail (`AdminOrderShowPage`):** One desktop **table** for both Products and Packs so column widths align; header **`admin.orders.line_name`** (Nom / Nombre / Name); order total uses **`text-end`**; **Llaves iguales** shows **No** when the field does not apply (no blank cells).

- **Vite dev:** `npm run dev` now sets **`LARAVEL_VITE_NO_AUTO_RELOAD=1`** with **cross-env** (Windows-safe), disabling WebSocket HMR, React Fast Refresh, and Laravel plugin full-page refresh; use **`npm run dev:hmr`** for the previous default. Added devDependency **cross-env**; **`vite.config.js`** uses explicit **`hmr: true`** when hot reload is enabled.

- **Admin / personalized solution:** **Send to client** opens a **confirmation dialog** before **`POST …/notify-resolution`** (**`email_client_confirm_*`** i18n).

- **Admin / personalized solution modal:** Removed the duplicate **Send to client** button from the modal footer; it remains on the detail toolbar.

## [0.1.28] - 2026-04-29

### Added

- **Admin layout:** Sidebar subtitle under the shop title shows **Admin** and the app semver (**`admin.sidebar.subtitle`**, same source as the storefront footer: **`APP_VERSION`** / `package.json`).

- **Admin settings · column visibility:** Registry (`config/admin_index_columns.php` + `adminIndexColumnsRegistry.js`) lists **all** list columns per table (IDs, product destacat/tendència, order installation/shipping/timestamps, personalized solution client login email, feature type ID, etc.). Matching `<th>` / `<td>` on admin index pages; new i18n keys under **`admin.common.*`**, **`admin.orders.column_*`**, **`admin.personalized_solutions.client_login_email`**, **`admin.features.feature_name_id`**.

### Changed

- **Cursor rules:** Clarify that **`footer.version`** / **`__APP_VERSION__`** come from root **`package.json`** (nothing auto-bumps per prompt); document **patch semver** on each shippable task before push and **`prod`** merge (**`app-version-cadence.mdc`**, **`commit-changelog-version.mdc`**, **`testing-verification.mdc`**).

- **Admin / personalized solution modal:** Status **select** uses **`select-sm`**, **`max-w-xs`**, and **`min-w-0`** so it does not stretch full modal width; modal title (**`resolution_modal_title`**) includes a **line break** after “o” / “or”; **`h2`** uses **`whitespace-pre-line`**.
- **Admin / personalized solution detail:** Toolbar **`admin.personalized_solutions.email_client_short`** label is **Enviar al cliente / Enviar al client / Send to client**; **`resolution_modal_open`** remains **Resolución / Resolució / Resolution**.
- **Storefront / order detail:** Status timeline no longer lists the synthetic **`current`** step (same label as the **Estado** badge above).
- **Storefront i18n:** **`shop.shipping_flat`** is shortened to **Envío / Enviament / Shipping** (removed flat-rate parenthetical); used on cart, checkout, and order detail totals.
- **Storefront / order detail (`/orders/:id`):** **Estado** and **fecha** are shown in a compact **summary card** (status badge, locale-aware date). Order lines match admin: **Productes** and **Packs** sections (hidden when empty), **totals** in a separate shaded footer; new i18n keys under **`shop.order.*`**.
- **Storefront / custom solution:** Success toast copy (**`shop.custom_solution.success`**) reminds the user to **check email** (ca / es / en).
- **Storefront / navbar:** The **`header-gradient-line`** loading animation tracks **all in-flight `api` (axios) requests**, not only **React Query**, so cart, profile, orders, checkout, etc. show the same bar as the product catalog (respects **`prefers-reduced-motion`**).

## [0.1.25] - 2026-04-28

### Changed

- **Admin order detail:** Order lines are split into **Products** and **Packs** (each block omitted when empty); desktop table and mobile cards reuse the same row layout as before.

- **Admin dashboard:** Filters next to postal code now include **year** (defaults to calendar anchor when “All”; narrows chart to Y vs Y−1) and **month** (optional; one month vs same month prior year). `GET admin/stats/sales-by-period` and `GET admin/stats/top-products` accept `year` and `month` query params; top sellers use the same period when either filter is set. Shared `admin.dashboard.filter_all` for all “All” options.

- **Order invoice (HTML):** `GET /api/v1/orders/{order}/invoice` now uses the same **logo and brand** sources as transactional mail (`MAIL_BRAND_LOGO_URL` / `MAIL_BRAND_DEFAULT_LOGO`, `MAIL_BRAND_DISPLAY_NAME`, optional `MAIL_FOOTER_CONTACT_LINE`), a clearer **header** (reference `ORD-*`, date, status), **bill-to** panel with shipping and optional installation address, line columns for **unit price** and **line amount**, a right-aligned **summary** block, and neutral typography suitable for print.
- **Transactional email:** The personalized-solution **acknowledgement** (`PersonalizedSolutionReceivedMail`) includes a **summary** of the request (problem text, optional phone and address lines, attachment filenames). Very long descriptions are truncated in the email with a note to open the portal for the full text.

### Added

- **Admin settings · list columns:** Visible columns on admin **index** tables (products, categories, clients, variant groups, orders, packs, administrators, personalized solutions, feature types, feature values) are configurable under **Configuration**; preferences persist in **`shop_settings`** (`admin_index_columns`, JSON). **`App\Support\AdminIndexColumns`** and **`GET/PUT admin/settings`** normalize values so unknown column ids never break the UI. React registry: **`config/admin_index_columns.php`** + **`resources/js/config/adminIndexColumnsRegistry.js`** (keep in sync; see **`.cursor/rules/admin-shop-settings.mdc`**).

- **Storefront catalog:** Query param **`packs_only=1`** on **`GET /api/v1/products`** returns **only packs** (same `category_id`, `feature_ids`, and `search` filters as the mixed catalog), SQL-paginated. The product list sidebar includes a **toggle** (“Packs only” / i18n) that sets this param.

- **Admin custom solution detail:** A **modal** (primary **Resolution / quote**) edits **resolution or budget** and **status** without opening the full edit screen; **`PATCH /api/v1/admin/personalized-solutions/{id}/resolution`** saves those fields. A short **Email client** action (toolbar and modal) calls the existing **notify-resolution** endpoint so the mail uses the **currently saved** resolution (tooltip reminds to save first after edits).

- **Admin sidebar:** `GET /api/v1/admin/nav-alerts` exposes whether any **orders** or **custom solutions** need attention (English status codes in DB: e.g. `pending`, `awaiting_payment`, `awaiting_installation_price`, `installation_pending`, installation quote `pending`; solutions `pending_review` or non-empty `improvement_feedback`). The admin layout shows a small **warning** dot on **Orders** and **Custom solutions** when applicable.

- **Transactional email:** If checkout finishes **without** a successful payment in the same request (Stripe redirect, PayPal, etc.), the client receives **`OrderPaymentPendingMail`**. When **`MAIL_ADMIN_NOTIFICATION_ADDRESS`** is set, ops also get **`OrderPaymentPendingAdminMail`**. New installation-quote checkouts also notify that address with **`OrderInstallationQuoteRequestedAdminMail`**. `OrderInstallationQuoteRequested` now carries the resolved mail locale from `Accept-Language`.

- **Checkout (demo / stacks):** Optional **`CHECKOUT_DEMO_SKIP_PAYMENT`** skips payment when set (demo environments without a configured PSP).

### Fixed

- **Events:** `Application::configure()->withEvents()` was scanning `app/Listeners` while the same classes were also registered in `AppServiceProvider`, so each `handle()` ran **twice** (duplicate transactional emails, etc.). Event discovery is disabled in `bootstrap/app.php`; listeners remain the explicit `Event::listen` registrations only.

### Changed

- **Developer docs:** API HTML under **`docs/phpdoc`** uses phpDocumentor’s **`default`** template (modern layout and search UI) instead of legacy **`clean`**; run **`composer phpdoc`** after changing `app/` or `routes/` PHPDoc.

- **Storefront / custom solution:** Removed the optional **“open case / access code”** block and hash scroll on **`/custom-solution`**. Short paths **`/client/personalized-solutions`**, **`/mi-solucion`**, and **`/my-solution`** now redirect to **`/custom-solution`** without a hash. Direct portal access remains **`/client/personalized-solutions/:token`**; removed dead **`resources/js/lib/personalizedSolutionCode.js`**.
- **Transactional email layout:** Default logo is **`public/images/serraller_solidaria_logo_key.png`** (config `mail.brand.default_logo` / `MAIL_BRAND_DEFAULT_LOGO`); optional `MAIL_BRAND_LOGO_URL` still overrides. **Visible** brand from `mail.brand.display_name` (`MAIL_BRAND_DISPLAY_NAME`, default Serralleria Solidària), not `MAIL_FROM_NAME`. Personalized-solution subjects and body use the request mail locale via `trans(..., $locale)`.
- **Personalized solution API:** 8s idempotency for duplicate POST (same IP, email, start of description) to cut double emails; storefront submit uses an in-flight ref on top of the confirmation modal.
- **`.env.example`:** Clarified Gmail SMTP variables (do not leave `MAIL_MAILER` empty) and `MAIL_ADMIN_NOTIFICATION_ADDRESS` for personalized-solution admin alerts.

### Fixed

- **Storefront (custom solution):** Duplicate acknowledgements are mitigated with an in-flight ref on submit, the confirmation modal single-confirm guard, and a short **POST** idempotency window; personalized-solution mail templates no longer add the footer URL or access-code block; subjects are explicitly translation-based for the mail locale.
- **Admin data explorer:** Session statement timeout is applied with MariaDB `max_statement_time`, MySQL 8.0.3+ `max_execution_time`, or skipped on older MySQL, so `POST /api/v1/admin/data-explorer/query` no longer fails with unknown system variable on MariaDB / legacy MySQL. Unsupported-variable errors from `SET SESSION` are ignored as a last resort. Aggregate values on the explorer page use `ca-ES` / `es-ES` number formatting for Catalan vs Spanish UI.

### Added

- **Tests:** **`AdminDataExplorerMysqlTimeoutTest`** locks in MySQL/MariaDB timeout `SET SESSION` resolution.

### Changed

- **Agent pipeline:** archived closed PayPal sandbox CSP/CORS console task (**`CLOSED-20260419-1734-paypal-sandbox-csp-cors-console-errors.md`**) under **`agents/tasks/done/2026/04/19/`**; **log reviewer** latest pass (**2026-04-19T18:13Z**) recorded in **`agents/001-log-reviewer/time-of-last-review.txt`**.

## [0.1.24] - 2026-04-28

### Changed

- **Release:** Patch version bump (`package.json` / `package-lock.json`) to trigger CI/CD rebuild and deploy.

## [0.1.23] - 2026-04-25

### Added

- **Admin shop configuration** (`/admin/settings`): persisted `shop_settings` (low stock / overstock rules with optional product ID blacklists, toggle for new custom solution requests), **recalculate automatic highlights** (`is_trending` on active products from rules), public **`GET /api/v1/shop/public-settings`**, and homepage featured list uses **`is_featured` OR `is_trending`** (no duplicate rows).

## [0.1.22] - 2026-04-25

### Changed

- **Storefront / custom solution (follow-up code):** Input placeholder is a **64‑hex example pattern** instead of the generic “64 characters” wording (ca / es / en).

## [0.1.21] - 2026-04-25

### Changed

- **Storefront / custom solution:** Removed the global **“* Campo obligatorio”** line above the main form (fields keep their own `*` labels).

## [0.1.20] - 2026-04-25

### Changed

- **Storefront / client personalized solution portal:** Removed the **“Solución personalizada”** link to `/custom-solution` from the bottom action row; only the delete request action remains.

## [0.1.19] - 2026-04-25

### Changed

- **Storefront / custom solution (follow-up code):** Lighter block surface (`bg-base-100`, `rounded-box`, `shadow`); lead text shortened; removed secondary hint and optional disclaimer under the code field (ca / es / en).

## [0.1.18] - 2026-04-25

### Changed

- **Storefront / custom solution:** The standalone **“Acceder con código”** page is removed. **`/custom-solution`** has a **discreet** optional block (border, no `alert-info`) to enter the **64‑hex access code** only; copy does not push URLs. Navigation uses [`resources/js/lib/personalizedSolutionCode.js`](resources/js/lib/personalizedSolutionCode.js). **Navbar / drawer / footer** links to the removed page are gone. **`/client/personalized-solutions`**, **`/mi-solucion`**, and **`/my-solution`** **redirect** to **`/custom-solution#custom-solution-followup`**; **`/client/personalized-solutions/:token`** (portal) is unchanged.

## [0.1.17] - 2026-04-25

### Added

- **Storefront / personalized solution access:** Page **`/client/personalized-solutions`** to paste a **64‑character code** or full portal URL (access page, later folded into 0.1.18). Links from **navbar**, **drawer**, **footer**, and a callout on **`/custom-solution`**. Short redirects **`/mi-solucion`** and **`/my-solution`**.

## [0.1.16] - 2026-04-25

### Fixed

- **Storefront / product detail (`/products/:id`):** The product page uses **`@tanstack/react-query`** (same as the catalog) for fetch state, so a **generic error** no longer flashes while `GET /api/v1/products/{id}` is still in flight (e.g. first paint, remount, or **React Strict Mode** when the aborted request’s `finally` had cleared loading). While pending: spinner and **Back** link; error text only on real failure or missing product.

## [0.1.15] - 2026-04-25

### Added

- **i18n (English):** Third language **`en`** for the React storefront and admin: `resources/js/locales/en.json`, `lang/en.json`, and `lang/en/mail.php` for server-side strings and emails. `config('app.available_locales')` is `['ca', 'es', 'en']`; the navbar and admin header add **English**; `MailLocale`, `GET` invoice HTML (`locale` query and `Accept-Language`), and Stripe Checkout `locale` accept English. `scripts/build_react_en_locale.py` can rebuild `en.json` from `es.json` (optional `deep-translator` in a venv). Developer note: `docs/email-notifications.md` and **`.cursor/rules/i18n.mdc`** list all three UI locales.

## [0.1.14] - 2026-04-25

### Fixed

- **Catalog / `GET /api/v1/products`:** Multiple `feature_ids` for the same characteristic (e.g. two brands) now use **OR**; different characteristics still combine with **AND**. Packs in mixed catalog use the same grouping.

### Added

- **Tests:** **`ProductCatalogFeatureFilterTest`** for same-characteristic OR and cross-characteristic AND on the product list API.

## [0.1.13] - 2026-04-24

### Fixed

- **App version:** Storefront and admin now use the same version as root **`package.json`**: Laravel **`config('app.version')`** reads that file at bootstrap (with optional **`APP_VERSION`** override); Vite injects **`__APP_VERSION__`** so **`resources/js/config/version.js`** no longer drifts from releases. Documented in **`.env.example`**.

### Changed

- **Agent pipeline:** closed GitHub [#22](https://github.com/Luipy56/laravel-ecommerce/issues/22) task archived as **`agents/tasks/done/2026/04/24/CLOSED-20260424-1602-version-not-displayed-correctly.md`**; removed **`agents/tasks/FEAT-20260424-1602-version-not-displayed-correctly.md`**.

## [0.1.12] - 2026-04-19

### Fixed

- **SQLite / Artisan:** In non-`production` environments, the app now creates the on-disk database file (and parent directory) when `DB_DATABASE` is a file path that does not exist yet, avoiding `Database file at path […] does not exist` on first `artisan` / web boot (e.g. `DB_DATABASE=/tmp/…` overrides). Documented in **`README.md`**.

### Changed

- **PayPal:** Checkout/pay payloads include **`paypal_mode`** (`sandbox`/`live`) next to **`client_id`** so the SPA matches Smart Buttons loader context with **`PAYPAL_MODE`** / REST hosts; **`PayPalInlineButtons`** documents the shared SDK URL and keys script elements by mode. **`docs/CONFIGURACION_PAGOS_CORREO.md`** CSP/CORS section ties **`bootstrap/app.php`** (no app CSP), REST vs SDK behaviour, and GitHub [#14](https://github.com/Luipy56/laravel-ecommerce/issues/14) / [#19](https://github.com/Luipy56/laravel-ecommerce/issues/19).

### Added

- **Tests:** **`SqliteDatabaseBootstrapTest`**; **`PayPalPaymentTest`** / **`CheckoutPaymentConfigTest`** extended for **`paypal_mode`** in payment config.

## [0.1.11] - 2026-04-19

### Added

- **Personalized solution client portal (token access without login):** `public_token` on **`personalized_solutions`**; public API **`/api/v1/public/personalized-solutions/{token}`** (read, update contact/address, opt-out, request improvements); storefront route **`/client/personalized-solutions/:token`** (`ClientPersonalizedSolutionPage`); **`PublicPersonalizedSolutionController`**; feature test **`PublicPersonalizedSolutionPortalTest`**.
- **Email:** Shared Blade layout under **`resources/views/emails/layouts/`**; customer **`PersonalizedSolutionResolvedMail`**; admin **`PersonalizedSolutionImprovementRequestedAdminMail`**; **POST** **`/api/v1/admin/personalized-solutions/{id}/notify-resolution`** to resend resolution. Order and installation-related customer templates updated to the shared layout.
- **Admin / model:** Improvement feedback fields (`iterations_count`, `improvement_feedback`, `improvement_feedback_at`); admin personalized solution show surfaces portal URL, status, and resolution actions.

### Changed

- **Custom solution** submit flow, **order** integration, **admin** personalized-solution edit/show, **lang** (ca/es, including `mail.php`), **`docs/email-notifications.md`**, **`.env.example`**, **`config/mail.php`**, and **`trash/diagramZero.dbml`** aligned with the portal and notification behaviour; **`PersonalizedSolutionSeeder`** updates.
- **Agent pipeline:** archived closed tasks under **`agents/tasks/done/2026/04/19/`** (replacing superseded **`agents/tasks/`** entries).

## [0.1.10] - 2026-04-19

### Added

- **Admin:** Data explorer for allowlisted tables — schema, filtered query with pagination, CSV export, and aggregates (**`AdminDataExplorerService`**, **`AdminDataExplorerController`**, **`config/admin_data_explorer.php`**); routes **`/api/v1/admin/data-explorer/*`** (throttled); React **`AdminDataExplorerPage`** and admin nav link; **`AdminDataExplorerTest`**; smoke list includes **`GET /api/v1/admin/data-explorer/schema`** in **`AdminUserJourneyTest`**.
- **Installation pricing:** Automatic tiered installation fees from merchandise subtotal when installation is requested; above **€1000** merchandise the flow awaits a **manual quote** (**`Order::INSTALLATION_MERCHANDISE_AUTOMATIC_MAX_EUR`**, **`Order::automaticInstallationFeeFromMerchandiseSubtotal()`**). Cart API adds **`installation_quote_required`** and **`installation_fee_eur`**; checkout sets **`installation_price`** / **`installation_status`** when priced. **`OrderInstallationPricingTest`**; **`CustomerTransactionalEmailTest`** covers automatic tier (no quote mail, payment confirmed).

### Changed

- **Checkout / cart:** Payment block and validation when installation is **priced** vs **awaiting a custom quote**; summary includes installation fee and estimated total; **`checkoutFormSchema`** takes installation options; Catalan/Spanish strings; **`CartContext`** and **`CartPage`** updated for installation metadata; add-line response includes cart id and **`installation_requested`**.
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-04-19T16:22Z).

## [0.1.9] - 2026-04-11

### Fixed

- **Checkout:** Payment method selector and submit wait for `GET /api/v1/payments/config` so only **enabled** methods appear (no flash of card+PayPal before config loads). PHPUnit clears `PAYMENTS_CHECKOUT_METHODS` from the developer `.env` so tests do not inherit a `paypal`-only whitelist.
- **Docs:** README and `docs/CONFIGURACION_PAGOS_CORREO.md` troubleshooting when only PayPal shows despite Stripe keys (`PAYMENTS_CHECKOUT_METHODS` whitelist).

### Added

- **`CheckoutPaymentConfigTest`:** asserts `data.methods.card` and `data.methods.paypal` are both true when both providers are configured and whitelisted.

## [0.1.8] - 2026-04-11

### Changed

- **PayPal (hosted redirect):** PayPal `approval_url` opens in a **new tab** so the storefront tab stays open; if the browser blocks the popup, a warning and a user-triggered link appear on checkout and order pay (`shop.payment.paypal_popup_blocked`, `shop.payment.paypal_open_link` in ca/es). Inline Smart Buttons are unchanged.
- **PayPal:** REST `createOrder` sends storefront **`return_url`** and **`cancel_url`** for hosted approval; cancelled or incomplete flows show explicit warnings (inline `onCancel`, return/cancel query handling); after opening approval in a new tab the client navigates to **order detail** with a short hint toast; the **orders list** shows a payment-due badge for payable unpaid orders. `PayPalPaymentTest` asserts these URLs in the create-order payload.
- Agent pipeline: archived closed PayPal tasks (`agents/tasks/done/2026/04/11/CLOSED-20260411-2000-checkout-paypal-open-new-tab-not-same-page.md`, `agents/tasks/done/2026/04/11/CLOSED-20260411-2015-paypal-cancel-failure-cart-retry.md`).
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-04-11T19:43Z).

## [0.1.7] - 2026-04-11

### Changed

- **Order detail:** Removed the blue simulated-payment development notice (`checkout.payment.simulated_mode_notice`) from the pay-again section; the key remains for checkout copy where needed.
- **Order detail:** Pending-payment notice no longer mentions PayPal explicitly (`shop.order.awaiting_payment_notice` in `ca` / `es`).
- **Storefront / Admin:** Unified global app toasts (`ToastProvider`): fixed **top-end** placement below the main navigation (with offsets on admin routes); a single stack for cart “added”, custom-solution submit success, admin success, and `emitAppToast` from non-React code. Success styling uses brand orange (`.alert-app-success`) instead of semantic green. Removed standalone `CartAddedToast`; `AdminToastProvider` delegates to `ToastContext`; `CartContext` triggers feedback via `emitAppToast`.
- **Profile:** Inline “saved” confirmation uses `alert-app-success` for consistency with the global success tone.
- Agent pipeline: archived closed unified app notifications task (`agents/tasks/done/2026/04/11/CLOSED-20260411-1800-unified-app-notifications-top-right-orange-success.md`).

### Removed

- **Storefront:** `PayPalUserEducation` (large blue `alert-info` blocks) removed from checkout and order pay; related i18n keys removed. Short `checkout.payment.paypal_help` next to PayPal buttons is unchanged.

## [0.1.6] - 2026-04-11

### Changed

- **PayPal checkout:** New order status **`awaiting_payment`** until the server captures the payment; then the order moves to **`pending`** as before. Customer invoice download and the orders list invoice link require a successful payment. Catalan and Spanish copy on the order detail page when payment is still pending.
- **Checkout:** Payment section no longer uses large blue `alert-info` banners; simulated mode and credential hints use muted small text. **`PAYMENTS_CHECKOUT_METHODS`** is documented in **`config/payments.php`**, **`.env.example`**, and **`docs/CONFIGURACION_PAGOS_CORREO.md`** (empty = card + PayPal; `paypal`-only hides Stripe card even when `STRIPE_*` are set).
- Agent pipeline: archived closed checkout payment UI task and PayPal defer-until-captured task (`agents/tasks/done/2026/04/11/CLOSED-20260411-1900-checkout-remove-info-alerts-stripe-methods.md`, `agents/tasks/done/2026/04/11/CLOSED-20260411-1901-paypal-defer-order-until-payment-captured.md`).
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-04-11T18:44Z).

## [0.1.5] - 2026-04-11

### Added

- **Storefront:** Customer purchased products (**`/purchases`**): authenticated **`GET /api/v1/purchases`** with pagination and optional **`date_from`** / **`date_to`** filters; rows link to product or pack detail and to the originating order; user menu entry alongside Orders (GitHub **#9**).
- **Tests:** `PurchasedProductsTest`; `ProductCatalogIndexPaginationTest` asserts paginated mixed catalog responses for infinite-scroll clients.

### Fixed

- **Storefront:** Rapid catalog search no longer shows a misleading “could not connect” toast when React Query aborts a superseded `GET /api/v1/products` request (axios cancellation is no longer treated as a network failure in `resources/js/api.js`).

### Changed

- **Payments:** Checkout uses **Stripe Checkout** (hosted redirect) for card/wallets/Bizum (configurable `STRIPE_CHECKOUT_PAYMENT_METHOD_TYPES`); PayPal exposes **`approval_url`** when the REST order includes an approve link (redirect before Smart Buttons). Stripe webhooks handle **`checkout.session.completed`** with idempotency (`stripe_webhook_events` table); Redsys/Revolut removed from storefront and public webhook routes. `PAYMENTS_CHECKOUT_METHODS` accepts only `card` and `paypal`.
- **Storefront:** Product catalog (`/products`, `/categories/:id/products`) uses **infinite scroll** (incremental `page` loads via Intersection Observer) instead of prev/next pagination; legacy `?page=` query params are ignored/stripped.
- Agent pipeline: archived closed Laravel search service PostgreSQL `pg_trgm` task (`agents/tasks/done/2026/03/30/CLOSED-20260330-1825-laravel-search-service-postgresql-trgm.md`).
- Agent pipeline: archived closed Elasticsearch Scout mapping / queue sync task and catalog search API task (Elasticsearch primary, PostgreSQL fallback) (`agents/tasks/done/2026/03/30/CLOSED-20260330-1835-elasticsearch-scout-mapping-queue-sync.md`, `agents/tasks/done/2026/03/30/CLOSED-20260330-1845-search-api-elasticsearch-primary-postgresql-fallback.md`).
- Agent pipeline: archived closed customer purchased products view task (`agents/tasks/done/2026/04/11/CLOSED-20260411-1617-customer-purchased-products-view.md`).
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-04-11T16:57Z).

## [0.1.4] - 2026-03-31

### Added

- **Search operator tooling:** Artisan **`products:reindex-elasticsearch`** (flush / recreate index / import with optional `--chunk` and `--queued`; skips safely when Scout is not on Elasticsearch). **`products:rebuild-search-text`** gains **`--stale`** and **`--chunk`**. **`SearchDemoProductSeeder`** adds deterministic `SEARCH-DEMO-*` products for manual search checks after **`migrate:fresh --seed`**.
- **Tests:** `ReindexElasticsearchProductsCommandTest`, PostgreSQL **`idx_products_search_text_trgm`** presence check, and stale rebuild coverage in **`ProductSearchTextTest`** (catalog search fallback remains covered in **`ProductCatalogSearchApiTest`**).
- **Search synonyms:** `config/search_synonyms.php` drives token expansion in `ProductSearchService` (PostgreSQL / SQLite paths) and merges an Elasticsearch `synonym_graph` + `product_synonym` analyzer into the products index definition when groups are non-empty. **`config/search_locales.php`** documents future multilingual index strategies without schema changes.

### Changed

- **Storefront:** Navbar catalog search updates the URL after **300 ms** debounce while typing; preserves **`category_id`** and **`feature_id`** filters on **`/products`** and **`/categories/:slug/products`**. Submit runs navigation immediately (clears pending debounce).
- Agent pipeline: PostgreSQL extensions / search-text GIN task tracking renamed from **`UNTESTED`** to **`WIP`** (`agents/tasks/WIP-20260330-1815-postgresql-extensions-search-text-gin.md`).
- Agent pipeline: archived closed Scout **`EngineManager`** boot binding task (**`CLOSED-20260331-0943-scout-enginemanager-binding-resolutionexception.md`**) under **`agents/tasks/done/2026/03/31/`**.
- Agent pipeline: closed storefront navbar debounced search task (**`agents/tasks/done/2026/03/31/CLOSED-20260331-1200-storefront-searchbar-debounced-live-update.md`**).
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-03-31T10:03Z).

## [0.1.3] - 2026-03-30

### Added

- Product catalog **`search_text`** (normalized name, code, description) with PostgreSQL **`pg_trgm` GIN** index on `products.search_text`; on PostgreSQL the baseline migration enables **`pg_trgm`**, **`citext`**, and **`unaccent`**, and uses **`citext`** for `clients.login_email` and `password_reset_tokens.email`.
- **`App\Services\ProductSearchTextRebuildService`**, contract **`RebuildsProductSearchText`**, and Artisan **`products:rebuild-search-text`** to rebuild `search_text` in bulk.
- **Catalog search** over HTTP: **`GET /api/v1/products/search`** (validated query and limit; **Elasticsearch** via Scout when configured, **PostgreSQL** `ILIKE` plus `pg_trgm` word similarity / similarity when on `pgsql`, token-wise `LIKE` on other drivers) with **`throttle:60,1`**; tuning via **`config/product_search.php`**.
- **Laravel Scout** with optional **Elasticsearch** for `Product`: custom **`App\Scout\ElasticsearchEngine`** using **`elasticsearch/elasticsearch`** (Scout 11–compatible), completion-oriented mapping for future autocomplete, queued **`MakeSearchable`** / **`RemoveFromSearch`** when **`SCOUT_QUEUE=true`**, default **`SCOUT_DRIVER=null`** for CI and environments without a cluster. See **`docs/elasticsearch.md`**.
- **Documentation:** **`docs/postgresql.md`** (PostgreSQL setup, `pdo_pgsql`, SSL, extensions, search); Elasticsearch notes in **`docs/elasticsearch.md`**.
- **Tests:** product search text, catalog search API, PostgreSQL search service, Scout indexing, optional live Elasticsearch integration (**`ES_TEST_HOST`**), and Scout mapping unit coverage.
- Agent task files under **`agents/tasks/`** for search/PostgreSQL/Elasticsearch work items and a closed **`db:show`** connectivity task archive.

### Changed

- **`.env.example`:** commented PostgreSQL and MySQL connectivity guidance (**`DB_SSLMODE`**, **`127.0.0.1`** vs **`localhost`**, Docker hostnames), plus Scout and Elasticsearch variables (**`ES_TEST_HOST`** for integration tests).
- **`README.md`:** PDO extension requirements per database driver, PostgreSQL recommendation, connectivity troubleshooting, link to **`docs/postgresql.md`**.
- **`config/database.php`:** PostgreSQL schema search path and SSL mode from environment.
- **`AppServiceProvider`:** bindings for catalog search and product search-text rebuild abstractions.
- **`Product` model:** `search_text` in **`$fillable`**, Scout **`Searchable`**, and search-text normalization helpers.
- **`ProductController`:** catalog search action wired to the search service contract.
- **`ProductSeeder`:** populates **`search_text`** for seeded products.
- **`phpunit.xml`:** default **`ES_TEST_HOST`** for optional Elasticsearch feature tests.
- **`routes/api.php`:** **`throttle:60,1`** on **`products/search`**.
- **`trash/diagramZero.dbml`:** schema notes aligned with search-related columns and PostgreSQL indexes.
- Agent log reviewer: latest pass appended to **`agents/001-log-reviewer/time-of-last-review.txt`** (2026-03-30T10:12Z).

### Fixed

- **`CustomerTransactionalEmailTest`:** set **`payments.checkout_method_keys`** in simulated Bizum scenarios so checkout payment config matches test expectations.

## [0.1.2] - 2026-03-29

### Added

- `scripts/gh-bootstrap-agent-labels.sh`: idempotent `gh` helper to create GitHub labels used by the multi-agent workflow (`agent:planned`, `agent:wip`, `agent:testing`, `production-urgent`).

### Changed

- PayPal storefront: SDK script URL uses `intent=capture` and `commit=true`; checkout copy (ca/es) and `docs/CONFIGURACION_PAGOS_CORREO.md` clarify popup/overlay vs full-page redirect and that server capture implies PayPal-side approval.
- Agent pipeline (GitHub #2 smoke): removed queue pickup `agents/tasks/UNTESTED-20260327-1614-test.md`; updated archived `agents/tasks/done/2026/03/27/CLOSED-20260327-1614-test.md` with tester report; added `agents/tasks/done/2026/03/27/CLOSED-20260327-1614-test-hello-world-coder-artifact.md` for verification traceability.
- Agent pipeline: closed PayPal sandbox E2E verification task (`agents/tasks/UNTESTED-20260327-1401-paypal-checkout-sandbox-e2e.md` → `agents/tasks/done/2026/03/27/CLOSED-20260327-1401-paypal-checkout-sandbox-e2e.md` with tester report); `agents/001-log-reviewer/time-of-last-review.txt` updated.
- Agent pipeline: archived PayPal buyer-approval UI task (`agents/tasks/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md` → `agents/tasks/done/2026/03/27/CLOSED-20260327-1745-paypal-approval-ui-popup-vs-redirect.md`).
- Agent log reviewer: latest pass appended to `agents/001-log-reviewer/time-of-last-review.txt` (2026-03-27T18:14Z).
- Agent pipeline: Stripe order-pay task tracking moved to `agents/tasks/WIP-20260329-2114-stripe-not-configured-order-pay.md` (coder + tester notes).

### Fixed

- Order pay / Stripe: when card checkout is started without valid Stripe keys, respond with **422** and `code: payment_method_not_configured` (translated message) instead of treating it as an application failure; `PaymentProviderNotConfiguredException` is not reported to the log; `GET /api/v1/payments/config` availability aligns with the same credential rules as checkout start (`dontReport` in `bootstrap/app.php`).

## [0.1.1] - 2026-03-27

### Added

- Multi-agent workflow documentation and tooling (`AGENTS.md`, `agents/`, `docs/agent-loop.md`, `docs/agent-cursor-rules.md`, `scripts/git-sync-agent-branch.sh`).
- Cursor rules for commit/changelog/version workflow and git integration branch policy (`.cursor/rules/commit-changelog-version.mdc`, `.cursor/rules/git-agent-branch-workflow.mdc`).
- PayPal sandbox E2E operator checklist in `docs/CONFIGURACION_PAGOS_CORREO.md`.
- Feature test for PayPal-only checkout payments config (`CheckoutPaymentConfigTest`).

### Changed

- Default multi-agent integration branch is **`agentdevelop`** (`AGENT_GIT_BRANCH` overrides). Sync script and docs updated accordingly.
- **`laravel-ecommerce-agent-loop.sh`:** invoke **`cursor-agent`** with **`--print`** and the prompt file contents (current CLI; `-p` is `--print`, not a path).
- **`.cursor/rules/auth.mdc`:** cross-link to testing verification for unauthenticated `api/*` behaviour.

### Removed

- Obsolete agent task file `agents/tasks/UNTESTED-20260327-1542-paypal-authorizedjson-stdclass-typeerror.md`.
