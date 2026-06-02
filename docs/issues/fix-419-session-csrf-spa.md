# Fix intermitente HTTP 419 (sesión / CSRF) en producción — SPA

## Resumen

En producción, a veces aparece **419 Page Expired** al iniciar sesión o en otras acciones. **Recargar la página (o volver atrás y recargar) lo soluciona**, pero un usuario normal no sabe hacerlo y puede abandonar el flujo.

Investigar causas raíz y aplicar una **recuperación automática inteligente** además de mejoras preventivas.

## Contexto actual

| Pieza | Comportamiento |
|-------|----------------|
| **API REST** | Rutas bajo `/api/v1/*` con middleware `web`; CSRF **excluido** para `api/*` en `bootstrap/app.php`. Login `POST /api/v1/login` **no debería** fallar por token CSRF. |
| **Rutas web no-API** | p. ej. `POST /auth/google/redirect` — **sí** requieren CSRF (`_token` / cookie `XSRF-TOKEN`). Sin token válido → **419**. |
| **Cliente axios** | `resources/js/api.js` — `withCredentials`, lee cookie `XSRF-TOKEN` por petición (correcto tras `session()->regenerate()`). |
| **bootstrap.js** | Fija `X-CSRF-TOKEN` desde `<meta name="csrf-token">` en **carga inicial** — puede quedar **obsoleto** si algo usa `window.axios` en lugar de `api`. |
| **Interceptor 419** | Toast + redirección a `/session-expired` (storefront) o `/admin/login` (admin). **Excepciones:** ya en `/admin/login` o `/session-expired` → no redirige (solo toast/reject). **`/login` no está excluido.** |
| **SessionExpiredPage** | Botón “Recargar” manual + enlace a login. |
| **Sesión** | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120` min (`.env.example`). |
| **SPA** | Una sola carga de `welcome.blade.php`; pestaña abierta horas → sesión/cookies pueden caducar sin que la UI lo detecte. |

## Síntoma reportado

- Ocurre **en producción**, de forma **intermitente**.
- Ámbitos: login y “otros” (carrito, checkout, Google Sign-In, etc.).
- Workaround humano: **atrás + recargar** → vuelve a funcionar.

## Hipótesis a investigar (prioridad)

1. **Pestaña inactiva / sesión caducada (120 min)**  
   HTML y meta CSRF de la carga inicial siguen en memoria; primera acción POST (sobre todo **Google OAuth** vía formulario web) falla con 419.

2. **Token CSRF obsoleto en formularios web**  
   `GoogleSignInSection` lee `meta[name="csrf-token"]` al montar; no se refresca al volver a la pestaña ni tras regenerar sesión en otra pestaña.

3. **Múltiples réplicas sin sticky sessions**  
   Si hay >1 contenedor PHP y sesión en fichero/DB mal compartida, cookie de sesión puede apuntar a instancia sin esa sesión → comportamiento errático (a veces 419/401).

4. **Cookies en producción**  
   `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, `SameSite`, proxy HTTPS (`TrustProxies` / `APP_URL`) mal configurados.

5. **Confusión UX**  
   Login API devuelve 422/401 pero otra petición paralela (p. ej. merge carrito, `GET user`) o **Google redirect** devuelve 419; el usuario lo asocia al login.

6. **Caché CDN/HTML**  
   HTML cacheado con CSRF antiguo (menos probable si solo SPA shell, pero revisar en prod).

## Enfoque de solución (capas)

### Capa 1 — Recuperación automática (UX, quick win)

Implementar en el cliente (y solo reintentar **una vez** por petición para evitar bucles):

```
POST falla con 419
  → si no es reintento: GET “sanctum/csrf-cookie” o GET / (o endpoint dedicado /csrf-refresh) para renovar cookies
  → reintentar la petición original
  → si sigue 419:
       - en /login, /register, /forgot-password, /reset-password: window.location.reload() automático (1 vez por sesión de pestaña, sessionStorage flag)
       - resto: mantener /session-expired pero con auto-reload opcional tras 2 s + mensaje claro
```

**Alternativa simple (sugerida por negocio):** en rutas de auth storefront, ante 419 → **`location.reload()` inmediato** una vez, sin pantalla intermedia. Documentar en i18n un toast breve: “Sesión renovada, inténtalo de nuevo”.

Patrón habitual en Laravel + SPA (Sanctum docs, issues de axios): **refresh CSRF cookie + retry once**.

### Capa 2 — Prevención

- **`visibilitychange` / `focus`:** al volver a la pestaña, petición ligera autenticada o `GET /api/v1/user` (ya existe) para refrescar cookies de sesión; opcional endpoint `GET /api/v1/csrf-ping` que devuelva 204 y renueve `XSRF-TOKEN`.
- **Google OAuth:** antes de submit del form, refrescar token (fetch + actualizar `meta` y hidden `_token`) o migrar redirect a flujo 100 % API si se unifica CSRF.
- **Eliminar o alinear `bootstrap.js`:** no fijar `X-CSRF-TOKEN` estático en `window.axios` si no se usa; todo el tráfico debería pasar por `api.js` con cookie XSRF.
- **Login/register en interceptor:** añadir `/login`, `/register` a rutas que disparan **reload** en lugar de `/session-expired` cuando el retry falla.

### Capa 3 — Infra / configuración (checklist prod)

Documentar en `docs/` (o ampliar troubleshooting):

- [ ] `SESSION_DRIVER=database` (o redis) compartido entre réplicas
- [ ] Sticky sessions en load balancer **o** sesión centralizada
- [ ] `SESSION_SECURE_COOKIE=true` detrás de HTTPS
- [ ] `SESSION_DOMAIN` correcto (vacío o dominio raíz; no incorrecto con subdominio)
- [ ] `APP_URL` coincide con URL pública
- [ ] `TrustProxies` configurado si hay reverse proxy

### Capa 4 — Observabilidad

- Log estructurado en backend cuando ocurre `TokenMismatchException` (ruta, user-agent, IP, si había cookie de sesión) — **sin** loguear tokens.
- Métrica opcional: contador 419 por ruta en prod para validar el fix.

## Criterios de aceptación

- [ ] Reproducir escenario “pestaña en login > SESSION_LIFETIME” en staging: tras fix, login u OAuth **recupera solo** (reload o retry) sin intervención del usuario en ≥95% de casos.
- [ ] Una petición 419 transitoria **reintenta una vez** tras refrescar cookie CSRF antes de mostrar error.
- [ ] `/login` y `/register`: no dejar al usuario en callejón sin salida (auto-reload o retry exitoso).
- [ ] No bucle infinito de reloads (guard `sessionStorage` o contador max 1).
- [ ] Google Sign-In (`POST /auth/google/redirect`) probado tras pestaña inactiva.
- [ ] Tests: feature test 419 en ruta web CSRF-protected; test unitario/interceptor (Jest o prueba manual documentada) del retry.
- [ ] Documentación operativa prod (checklist cookies/sesión).
- [ ] Mensajes i18n ca/es/en actualizados si cambia el copy de error.

## Fuera de alcance

- Desactivar CSRF globalmente (inaceptable en rutas web).
- Migrar a autenticación solo JWT sin cookies (cambio arquitectónico grande).
- Aumentar `SESSION_LIFETIME` como único “fix” (puede ayudar, no sustituye recuperación).

## Archivos probables

- `resources/js/api.js` — interceptor 419, retry, rutas auth
- `resources/js/bootstrap.js` — token estático
- `resources/js/components/GoogleSignInSection.jsx` — CSRF en form
- `resources/js/Pages/SessionExpiredPage.jsx` — UX recovery
- `resources/js/Pages/LoginPage.jsx` — manejo error login
- `bootstrap/app.php` — excepciones CSRF (solo si hay rutas mal clasificadas)
- `config/session.php`, `.env.example`
- Nuevo: `docs/TROUBLESHOOTING_419.md` o sección en README

## Verificación manual

1. Abrir `/login`, esperar >2 h (o forzar expiración borrando cookies de sesión dejando HTML abierto) → intentar login → debe recuperar.
2. Mismo con botón Google.
3. Añadir al carrito tras inactividad prolongada.
4. Dos pestañas: login en una, acción POST en otra.
5. Validar en prod tras despliegue con logs 419.

## Referencias externas

- [Laravel CSRF](https://laravel.com/docs/12.x/csrf)
- [Laravel Sanctum SPA — CSRF cookie](https://laravel.com/docs/12.x/sanctum#csrf-protection) (patrón aplicable aunque no usemos Sanctum para auth)
- Discusiones habituales: axios interceptor 419 → refresh `XSRF-TOKEN` cookie → retry once

## Labels sugeridos

`bug`, `auth`, `storefront`, `production`
