# NO email

## GitHub
- **Issue:** https://github.com/Luipy56/laravel-ecommerce/issues/6

## Problem / goal
Los clientes finales no reciben correos tras comprar, solicitar soluciones a medida u otros flujos similares. Hay que añadir notificaciones por email con confirmación y la información necesaria para cada caso. Ver documentación del repo (`docs/`, `README`) si aplica para despliegue y variables de entorno.

## High-level instructions for coder
- Revisar configuración de correo (driver, cola, remitente, plantillas) y qué eventos del dominio (pedidos, solicitudes, etc.) deberían disparar un email.
- Implementar o conectar el envío transaccional para los escenarios del issue; cadenas visibles al usuario en **ca** y **es** según normas del proyecto.
- Cubrir con pruebas automatizadas lo razonable (por ejemplo driver `array` / log en entorno de test) sin incrustar secretos reales de SMTP.

## Coder implementation notes (UTC 2026-03-30)

- La aplicación **ya** enviaba correos transaccionales de forma síncrona vía listeners en `AppServiceProvider` (`OrderPaymentSucceeded`, `OrderInstallationQuoteRequested`, `InstallationPriceWasAssigned`, `PersonalizedSolutionSubmitted`, `OrderShipped`) y `PaymentCompletionService::markSucceeded()`. Las plantillas y textos **ca/es** están en `lang/*/mail.php` y `resources/views/emails/*`.
- La causa habitual de “no llega el correo” en despliegue es **`MAIL_MAILER=log`** (valor por defecto en `.env.example`): los mensajes solo van al log. Se ampliaron comentarios en **`.env.example`** y la sección **Troubleshooting** en **`docs/email-notifications.md`**.
- Tests: **`CustomerTransactionalEmailTest`** — se añadieron casos para **`POST /api/v1/orders/{order}/pay`** (pago aplazado con Bizum simulado) y para **asignación de precio de instalación** por admin (`InstallationPriceAssignedMail`).

## Testing instructions (tester)

1. `php artisan test` — debe pasar (incluye `CustomerTransactionalEmailTest`).
2. `php artisan routes:smoke` — ningún GET con HTTP 500.
3. Revisión manual opcional en staging: configurar **`MAIL_MAILER=smtp`** (u otro transporte real) y comprobar bandeja del cliente en: checkout con pago simulado o real, pedido con instalación (presupuesto + precio asignado), formulario de solución a medida, cambio de estado a en tránsito en admin.
4. Si en producción siguen sin llegar correos: confirmar que **`MAIL_MAILER` no sea `log`**, remitente verificado y webhooks PSP configurados (ver `docs/email-notifications.md`).

---

## Test report (tester, UTC)

1. **Date/time (UTC) y ventana de log:** Inicio **2026-03-30 10:13:25 UTC**; fin aprox. **2026-03-30 10:14 UTC**. Ventana consultada en `storage/logs/laravel.log` (sin eventos de correo específicos atribuibles a esta corrida; la evidencia principal es la salida de PHPUnit / `routes:smoke`).

2. **Environment:** PHP **8.3.6** (CLI), rama **`agentdevelop`**, tests con entorno PHPUnit (no se forzó `APP_ENV=production`). Node no requerido por las instrucciones de esta tarea.

3. **What was tested:** Criterios 1–2 de «Testing instructions (tester)»; criterio 3 marcado como opcional en la propia tarea; criterio 4 es guía de troubleshooting, no verificación ejecutable aquí.

4. **Results:**
   - **`php artisan test` (incl. `CustomerTransactionalEmailTest`):** **PASS** — `Tests: 42 passed (201 assertions)`, `Duration: 1.64s`, incluye los 6 casos de `CustomerTransactionalEmailTest`.
   - **`php artisan routes:smoke`:** **PASS** — `All checked GET routes returned a non-500 status.`
   - **Revisión manual en staging con SMTP real:** **N/A** — opcional según tarea; no ejecutada en este paso.
   - **Comprobación producción / `MAIL_MAILER`:** **N/A** — documentación de operación, no prueba local.

5. **Overall:** **PASS** (criterios obligatorios automatizados cumplidos).

6. **Product owner feedback:** Los correos transaccionales quedan cubiertos por tests en los flujos clave (pago simulado, instalación, solución a medida, envío). En despliegue, si los usuarios no ven correos, lo habitual es **`MAIL_MAILER=log`** o transporte mal configurado; conviene validar una vez en staging con SMTP real antes de dar por cerrado el tema operativo.

7. **URLs tested:** **N/A — no browser** (solo CLI).

8. **Relevant log excerpts:** Evidencia principal = salida de consola:
   - `Tests:    42 passed (201 assertions)`
   - `All checked GET routes returned a non-500 status.`

**GitHub:** Comentario publicado en **#6** con resumen PASS (tester UTC 2026-03-30).
