# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Docker:** **`docker-compose.yml`** for local development (PostgreSQL, Nginx, PHP 8.2 FPM, Vite, queue worker, named volumes for `vendor` / `node_modules`), **`docker-compose.prod.yml`** for production builds, multi-stage **`docker/php/Dockerfile`**, **`docker/nginx/default.conf`**, **`docker/nginx/Dockerfile.prod`**, **`.dockerignore`**, and **`docker/php/docker-entrypoint.sh`**. **`config/trustedproxy.php`** wires **`TRUSTED_PROXIES`** into Laravel’s **`TrustProxies`** middleware. **`vite.config.js`** supports **`DOCKER=1`** for HMR behind the dev server.

### Changed

- **Storefront mobile drawer · account rows:** **Perfil**, **Comandes**, **Compres**, and guest **login** use the same **`drawerNavClass`** / **`drawerIconClass`** treatment as the main links (**`NavLink`** + **`IconUser`**, **`IconClipboardList`**, **`IconPackage`**, **`IconLogIn`**).

- **Storefront mobile drawer:** **Perfil** / **Comandes** / **Compres** (session) and guest **login** sit directly under the main nav list, separated by a thin **`hr`**; footer strip keeps language + brand only.

- **Storefront mobile drawer · locale panel:** Removed the redundant **close (X)** row inside the language card; the trigger, outside tap, and **Escape** remain sufficient.

- **Storefront mobile drawer (`Layout.jsx`):** Hamburger panel restyled (brand line, header close, icon + active state on primary nav, footer with brand/tagline, account shortcuts, language control). Shared **`STOREFRONT_LANGUAGE_OPTIONS`**; daisyUI **`dropdown-close`** when the locale menu is shut so the panel does not stay open on **`:focus-within`**; blur focused controls inside the widget; outside **`pointerdown`** (capture) to dismiss.
- **Navbar:** Below **`lg`**, hide the locale control and guest **login** (available in the mobile drawer). Navbar locale dropdown uses the same **`dropdown-open` / `dropdown-close`** pattern as the drawer; sync **`locale`** state when **`i18n.language`** changes.
- **Icons (`icons/index.jsx`):** **`IconHome`**, **`IconGrid`**, **`IconSparkles`**, **`IconHelpCircle`** for drawer links.

### Added

- **`resources/js/lib/storefrontLanguageOptions.js`:** Single source for storefront locale labels (ca / es / en) used by **Navbar** and **Layout**.

### Fixed

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
