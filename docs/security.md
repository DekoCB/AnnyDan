# Seguridad — AnnyDan

Estado verificado al cierre de la Fase 12/13 (2026-09-15). Cada punto indica cómo fue comprobado, no solo declarado.

## Autenticación

- Laravel Breeze (stack Blade), hashing de contraseñas con `Hash::` (bcrypt, `BCRYPT_ROUNDS=12`).
- Rate limiting nativo en login: 5 intentos por combinación email+IP antes de bloqueo temporal (`App\Http\Requests\Auth\LoginRequest`, sin modificar).
- Sesiones basadas en cookies firmadas (`SESSION_DRIVER=database`), regeneradas en cada login.
- **Riesgo pendiente identificado:** el registro público (`/register`) sigue abierto. Un usuario autorregistrado no obtiene ningún rol y por lo tanto no puede ejecutar ninguna acción de negocio (todas las Policies exigen un permiso que nunca tiene) — no es una vía de escalamiento de privilegios, pero es superficie de ataque innecesaria para una herramienta interna. Recomendado cerrarlo o convertirlo en alta por invitación (ver informe final, sección de recomendaciones).

## Autorización

- Policies de Laravel + spatie/laravel-permission, permisos con formato `modulo.accion`.
- Verificado explícitamente con tests que un usuario sin permiso no puede ejecutar la acción llamando al endpoint directamente (bypass de UI) — `tests/Feature/SecurityTest.php`.
- `Gate::before` en `AppServiceProvider` concede bypass total solo al rol ADMINISTRADOR.

## Protección contra vulnerabilidades comunes (OWASP)

| Vulnerabilidad | Protección | Verificación |
|---|---|---|
| XSS | Blade escapa por defecto (`{{ }}`); cero usos de `{!! !!}` en toda la vista | `grep` de todo `resources/views` + test dedicado con payload `<script>` |
| Inyección SQL | Eloquent/Query Builder parametriza bindings; cero `whereRaw`/`DB::raw` con input interpolado | `grep` de todo `app/` + test con payload de inyección clásico |
| CSRF | Middleware `VerifyCsrfToken` de Laravel sin excepciones; `@csrf` en todos los formularios | Revisión de `bootstrap/app.php` (sin `except`) |
| Mass assignment | Cada modelo declara `$fillable` explícito; todo Controller usa `$request->validated()`, nunca `$request->all()` | `grep -rL "\$fillable" app/Models` (vacío) + `grep "request->all"` (vacío) + test que envía `estado` no listado en las reglas |
| Fuga de stack traces | `APP_DEBUG` controlado por entorno; manejo de excepciones de Laravel por defecto | Configuración estándar, no modificada |
| Dependencias vulnerables | `composer audit` y `npm audit` | 0 advisories en ambos, verificado en Fase 14 |

## Auditoría e inmutabilidad

- Cada operación de escritura relevante (crear/anular venta, registrar pago, crear CxC/CxP, emitir/anular boleta, crear/desactivar cliente/proveedor, registrar movimiento de caja) queda registrada vía `AuditoriaService::registrar()`: usuario, acción, fecha/hora, registro afectado, valores antes/después, IP, user agent.
- **No existe ninguna ruta para editar o borrar un registro de auditoría.** Verificado con un test que confirma que `PATCH`/`PUT`/`DELETE` a `/auditoria/{id}` devuelven `405 Method Not Allowed` (el verbo se rechaza a nivel de enrutamiento, antes de llegar a cualquier Controller).
- **Límite honesto:** esta protección es de aplicación, no de motor de base de datos. No se configuró un usuario de MySQL con permisos restringidos (el entorno de desarrollo usa `root` compartido de XAMPP) — en un despliegue real se recomienda un usuario de aplicación sin `DELETE`/`UPDATE` sobre `auditorias`.

## Integridad financiera

- Toda operación con más de una escritura relacionada (registrar pago → actualizar saldo → actualizar estado → crear movimiento de caja → auditar) se ejecuta dentro de una única `DB::transaction()`. Un fallo revierte todo el conjunto.
- Los cálculos financieros (`CalculoVentaService`) y la numeración de comprobantes/operaciones (`CorrelativoService`, con `lockForUpdate()`) están centralizados — nunca duplicados entre Controllers.
- Ningún endpoint acepta un total, saldo o número de comprobante enviado por el cliente como autoritativo; el backend siempre recalcula o genera esos valores.

## No verificado / fuera de alcance de este MVP

- **Cobertura de pruebas (%).** No se instaló driver de cobertura (xdebug/pcov) en este entorno — no se reporta un porcentaje inventado. Se reporta en cambio el conteo real de tests (127, ver `docs/testing.md`).
- **Pentesting externo / escaneo de vulnerabilidades activo** (ej. OWASP ZAP). No ejecutado — recomendado antes de un despliegue productivo con datos reales.
- **Facturación electrónica SUNAT.** No implementada intencionalmente (sin credenciales ni requisitos formales) — el esquema de `comprobantes` está preparado para una integración futura, pero no hay XML, firma digital ni llamadas a servicios externos.
