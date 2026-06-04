# Deployment — stage & production (Docker VPS)

## Flow

```text
Developer (local or VPS)
    → commit + push `autoagents`
    → GitHub: stage.yml (tests + optional deploy)
    → https://stage-serra.ldeluipy.es
    → Cliente + Dev validan

When approved:
    → merge `autoagents` → `prod` (PR or local merge)
    → GitHub: prod.yml (tests + optional deploy)
    → https://serra.ldeluipy.es
```

| Entorno | Rama Git | URL | Ruta VPS |
|---------|----------|-----|----------|
| **Staging** | `autoagents` | https://stage-serra.ldeluipy.es | `/srv/serra/stage` |
| **Production** | `prod` | https://serra.ldeluipy.es | `/srv/serra/prod` |

`main` is the stable integration line (optional promotion from `autoagents`); production releases go through **`prod`**, not direct pushes to `main`.

## Local development

Use `docker-compose.yml` in the repo root on the developer machine. Do **not** use the production compose files for day-to-day coding.

## GitHub Actions

| Workflow | Trigger | Jobs |
|----------|---------|------|
| `stage.yml` | push `autoagents` | Test + build → deploy staging |
| `prod.yml` | push `prod` | Test + build → deploy production |
| `ci.yml` | push `main`, PRs | Test + build only |

Deploy jobs run **only** when the repo variable is `true`:

| Variable | Default | Effect |
|----------|---------|--------|
| `STAGE_DEPLOY_ENABLED` | `false` | SSH deploy to `/srv/serra/stage` |
| `DEPLOY_ENABLED` | `false` | SSH deploy to `/srv/serra/prod` |

### Secrets (same VPS for both)

| Secret | Example | Used by |
|--------|---------|---------|
| `PROD_HOST` | VPS IP or hostname | stage + prod |
| `PROD_USER` | `root` | stage + prod |
| `PROD_SSH_KEY` | deploy key private key | stage + prod |
| `PROD_PORT` | `22` (optional) | stage + prod |
| `STAGE_PATH` | `/srv/serra/stage` | stage deploy |
| `PROD_PATH` | `/srv/serra/prod` | prod deploy |

Enable deploy when paths and SSH are verified:

```bash
gh variable set STAGE_DEPLOY_ENABLED -b true -R Luipy56/laravel-ecommerce
gh variable set DEPLOY_ENABLED -b true -R Luipy56/laravel-ecommerce
```

## Manual deploy (SSH)

**Staging** (after `git push origin autoagents`):

```bash
cd /srv/serra/stage
git -C source fetch origin autoagents
git -C source reset --hard origin/autoagents
docker compose -f docker-compose.stage.yml build
docker compose -f docker-compose.stage.yml up -d
docker compose -f docker-compose.stage.yml exec -T app php artisan migrate --force --no-interaction
```

**Production** (after merge to `prod` and `git push origin prod`):

```bash
cd /srv/serra/prod
git -C source fetch origin prod
git -C source reset --hard origin/prod
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force --no-interaction
```

## Promote to production

1. Validate on staging (smoke: `/up`, login, admin, critical flows).
2. Merge `autoagents` into `prod` (PR recommended).
3. Push `prod` → workflow runs tests and deploy (if enabled).

```bash
git checkout prod
git pull origin prod
git merge origin/autoagents
git push origin prod
```

## Server layout

```text
/srv/serra/{stage|prod}/
├── .env                 # not in git
├── docker-compose.*.yml
└── source/              # git clone (branch per env)
```

Apache terminates TLS and proxies to Docker nginx on `127.0.0.1:18081` (stage) and `127.0.0.1:18080` (prod).
