# Serra marketing site (static / classic PHP)

Former OVH apex site for `serralleriasolidaria.cat`. Runs in a **dedicated** Docker stack (`serra-static`) — separate nginx + PHP-FPM from the Laravel ecommerce image.

## Ops (lu-zero)

```bash
cd /srv/serra/prod
# SMTP secrets live in /srv/serra/prod/.env.static (mode 600, not in git)
docker compose -f docker-compose.static.yml up -d --build
# Local smoke: curl -sI http://127.0.0.1:18083/
```

Edge Apache proxies the apex host to `127.0.0.1:18083`. Shop stays on `tienda.serralleriasolidaria.cat` → `18080` (Laravel).

## Contact form

`www/php/contact.php` reads `STATIC_SMTP_*` from the environment. Copy `.env.example` → `/srv/serra/prod/.env.static`.
