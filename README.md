# Laravel ecommerce

Headless-style **Laravel** backend (REST API + session auth) with a **React** storefront and admin UI built with **Vite**, **Tailwind CSS 4**, and **daisyUI 5**. Product catalogue, cart, checkout, payments integration, and admin CRUD are implemented in this repository.

## Features (high level)

- **Storefront:** React SPA (`resources/js/`) consuming the Laravel API.
- **Admin:** Protected admin area under `/admin` (same SPA stack).
- **API:** Versioned REST routes under `routes/api.php` (e.g. `api/v1/...`).
- **i18n:** User-facing UI targets **Catalan** and **Spanish** via Laravel lang and React i18n.

## Requirements

- PHP **8.2+** with PDO extensions matching your database driver:
  - **SQLite** (default in `.env.example` for local/CI): `pdo_sqlite`
  - **MySQL / MariaDB:** `pdo_mysql`
  - **PostgreSQL:** `pdo_pgsql` — required before setting `DB_CONNECTION=pgsql`; without it, Artisan fails with `could not find driver` (for example `php artisan db:show`).
- [Composer](https://getcomposer.org/)
- Node.js **18+** and npm
- A database supported by Laravel (MySQL, PostgreSQL, or SQLite), configured in `.env`

## Quick start

1. **Clone** the repository and enter the project directory.

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edit `.env` with your `APP_URL`, database credentials, and mail/payment variables as needed. PostgreSQL is recommended for production; see **`docs/postgresql.md`** for driver-specific options (e.g. local SSL).

4. **Database**

   ```bash
   php artisan migrate --seed
   ```

   During active development, when schema or seeders change, use:

   ```bash
   php artisan migrate:fresh --seed
   ```

   (Development only: this drops all tables and data.)

   **Connectivity:** With **SQLite**, no database server is required for migrations or `php artisan test` (default PHPUnit config). If `DB_DATABASE` points at an on-disk path (including under `/tmp/`) that does not exist yet, the application creates an empty database file automatically in non-production environments before connecting (equivalent to `touch` on that path). With **MySQL or PostgreSQL**, Artisan commands such as `php artisan db:show` open a real connection; fix `DB_HOST` / credentials and install the matching PDO extension first.

   - Prefer **`127.0.0.1`** over **`localhost`** for MySQL/MariaDB when you need TCP (many setups use a Unix socket for `localhost`, which breaks with Docker-only TCP or some remote hosts).
   - **`SQLSTATE[HY000] [2002] Network is unreachable`:** `DB_HOST` is unreachable (wrong hostname, database not running, firewall/VPN, or container networking). From a PHP container, use the Compose **service name** as `DB_HOST`, not `localhost`, unless the DB is in the same network namespace.

   **Optional catalog search:** With PostgreSQL, fuzzy search uses `pg_trgm` on `products.search_text`. Optional Elasticsearch indexing is documented in **`docs/elasticsearch.md`** (`products:reindex-elasticsearch`, `products:rebuild-search-text`).

5. **Frontend dependencies**

   ```bash
   npm ci
   ```

6. **Run locally**

   In two terminals (or your preferred process manager):

   ```bash
   php artisan serve
   ```

   ```bash
   npm run dev
   ```

   Open the app URL (e.g. `http://127.0.0.1:8000`). Vite serves and hot-reloads the React bundle.

7. **Production assets**

   ```bash
   npm run build
   ```

## Verification and smoke checks

After substantive changes, contributors typically run:

| Command | Purpose |
|--------|---------|
| `php artisan test` | Automated tests |
| `php artisan routes:smoke` | GET smoke on registered routes (no HTTP 500) |
| `npm run build` | Confirm the Vite/React build succeeds |

For checkout and payments work, see **`.cursor/rules/testing-verification.mdc`** in the repo (logged-in user, non-empty cart, `/checkout`, `GET /api/v1/payments/config`).

## Documentation

| Document | Contents |
|----------|----------|
| [`AGENTS.md`](AGENTS.md) | Contributor setup summary, smoke checks, optional agent orchestration |
| [`docs/CONFIGURACION_PAGOS_CORREO.md`](docs/CONFIGURACION_PAGOS_CORREO.md) | Payment provider environment variables (Stripe, PayPal, etc.). To offer **card and PayPal** at checkout, leave `PAYMENTS_CHECKOUT_METHODS` unset or empty (or set `card,paypal`); `paypal` alone hides Stripe even when `STRIPE_*` are set. |
| [`docs/email-notifications.md`](docs/email-notifications.md) | Email / notification configuration |
| [`docs/mobile-responsive.md`](docs/mobile-responsive.md) | Responsive UI notes |
| [`docs/postgresql.md`](docs/postgresql.md) | PostgreSQL setup, PHP `pdo_pgsql`, SSL, extensions, search |
| [`docs/agent-loop.md`](docs/agent-loop.md) | Task pipeline and labels (for teams using `agents/tasks/`) |
| [`docs/agent-cursor-rules.md`](docs/agent-cursor-rules.md) | Index of Cursor/project rules |

Do **not** commit secrets, API keys, or production credentials. Use `.env` (ignored by git) and document only variable *names* and safe examples.

## Screenshots and UI captures

The repository does not ship large binary screenshots by default. If you add visuals for contributors or operators, prefer a small set of images under **`docs/`** (e.g. `docs/screenshots/`) or link to stable external URLs in this README so the clone stays lightweight.

## Contributing

- Routine integration work often targets the **`agentdevelop`** branch; production promotion follows your team’s release process (see **`AGENTS.md`**).
- Follow existing code style, validation, and security practices (CSRF on web forms, Sanctum/API auth patterns, server-side validation).
- User-visible strings should remain translatable (Catalan / Spanish) per project standards.

## Security

Report security issues through your team’s private channel or issue policy. Do not open public issues for undisclosed vulnerabilities.

## License

This project is licensed under the **MIT** License (see `composer.json` → `"license": "MIT"`). Laravel and bundled dependencies retain their respective licenses.
