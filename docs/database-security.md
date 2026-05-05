# Database Security Policy

> Version: 2026-05-05

This document defines the database access control policy for the Serralleria Solidàia ecommerce application. It is part of the GDPR technical security measures (see `docs/gdpr-compliance.md §6`).

---

## 1. Principle of Least Privilege

The application database user must have the minimum permissions necessary:

| Permission | Application user | Migrations user | Backup user |
|---|---|---|---|
| `SELECT` | ✅ | ✅ | ✅ |
| `INSERT` | ✅ | ✅ | ❌ |
| `UPDATE` | ✅ | ✅ | ❌ |
| `DELETE` | ✅ | ✅ | ❌ |
| `CREATE TABLE` | ❌ | ✅ | ❌ |
| `DROP TABLE` | ❌ | ✅ | ❌ |
| `GRANT` | ❌ | ❌ | ❌ |

> The **application user** (used by Laravel at runtime) must **not** have `DROP`, `CREATE`, or `GRANT` permissions.

### PostgreSQL example

```sql
-- Create dedicated users
CREATE USER app_runtime WITH PASSWORD 'strong_random_password';
CREATE USER app_migrate WITH PASSWORD 'strong_random_password_2';

-- Runtime user: DML only
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_runtime;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_runtime;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO app_runtime;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO app_runtime;

-- Migrations user: full DDL + DML
GRANT ALL PRIVILEGES ON DATABASE app_db TO app_migrate;
```

### MySQL / MariaDB example

```sql
-- Runtime user: DML only
CREATE USER 'app_runtime'@'%' IDENTIFIED BY 'strong_random_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON app_db.* TO 'app_runtime'@'%';
FLUSH PRIVILEGES;

-- Migrations user: full DDL + DML
CREATE USER 'app_migrate'@'%' IDENTIFIED BY 'strong_random_password_2';
GRANT ALL PRIVILEGES ON app_db.* TO 'app_migrate'@'%';
FLUSH PRIVILEGES;
```

---

## 2. Encrypted Columns

The following columns are encrypted at rest using Laravel's `encrypted` cast (AES-256-CBC with the `APP_KEY`):

| Table | Column | Model |
|---|---|---|
| `clients` | `identification` | `Client` |
| `client_contacts` | `phone`, `phone2` | `ClientContact` |
| `personalized_solutions` | `problem_description`, `resolution`, `improvement_feedback` | `PersonalizedSolution` |

> **Important:** Direct SQL queries against these columns will see ciphertext. All reads/writes must go through the Eloquent models.  
> **Backup decryption:** Anyone with DB access + `APP_KEY` can decrypt. Rotate `APP_KEY` following the Laravel key rotation procedure if it is suspected to be compromised (this will invalidate all encrypted columns — plan a re-encryption script if needed).

---

## 3. Direct Database Access

- **Production direct access** (e.g. `psql`, `mysql`, DBeaver, Adminer) must:
  - Require 2FA / MFA for the SSH tunnel or DB client
  - Be logged at the server level (`pgaudit` for PostgreSQL, `general_log` for MySQL)
  - Never use the root / superuser account for application operations
- **No production credentials in `.env` files committed to git** — use secret management (e.g. environment secrets in CI/CD, Docker secrets, or a vault)

---

## 4. Backups

- Backups should be encrypted at rest (e.g. `gpg --symmetric` or cloud-level encryption)
- Backup files must not be stored in the same location as the live DB
- Restore drills should be run at least quarterly
- Backup retention: 30 days rolling (adjust per business requirement, but must cover the `gdpr:purge` run cycle)

---

## 5. Connection Security

- All database connections must use TLS (`sslmode=require` for PostgreSQL; `MYSQL_ATTR_SSL_CA` for MySQL)
- `APP_ENV=production` must be set so Laravel does not expose query logs or stack traces

---

## 6. Rotation and Incident Response

| Event | Action |
|---|---|
| Suspected `APP_KEY` compromise | Rotate key, re-encrypt all encrypted columns, invalidate all sessions |
| Suspected DB credential leak | Rotate credential immediately; audit access logs |
| Data breach affecting personal data | Notify AEPD within 72 hours (GDPR Art. 33); assess subject notification (Art. 34) |

---

## 7. Review Cadence

This document should be reviewed:
- At every major infrastructure change
- When a new sub-processor is added
- Annually as part of the GDPR compliance review
