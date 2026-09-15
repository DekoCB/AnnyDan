# Arquitectura — AnnyDan

## Stack

| Capa | Tecnología | Versión detectada |
|---|---|---|
| Backend | Laravel | 12.69.2 |
| Lenguaje | PHP | 8.2.12 |
| Base de datos | MariaDB (MySQL) | 10.4.32 |
| Frontend (render) | Blade | (núcleo de Laravel 12) |
| Frontend (lógica) | TypeScript | strict, target ES2021 |
| Build | Vite | 7.3.6 |
| CSS | Tailwind CSS | 3.x |
| Autenticación | Laravel Breeze | 2.4.2 (stack Blade) |
| Roles/permisos | spatie/laravel-permission | 6.25 |
| Testing | PHPUnit | 11.5.56 |
| Estilo | Laravel Pint | 1.30.4 |
| Análisis estático | Larastan / PHPStan | 3.12.1, nivel 5 |

## Capas y responsabilidades

```
USUARIO
   │
   ▼
BLADE (resources/views) ── presentación, sin lógica de negocio
   │
TypeScript (resources/ts) ── interactividad de UI, cálculos SOLO visuales
   │
LARAVEL
   ├─ Controllers (app/Http/Controllers) ── coordinan, delegan, nunca calculan
   ├─ Form Requests (app/Http/Requests) ── validación de entrada
   ├─ Policies (app/Policies) ── autorización por permiso
   ├─ Services (app/Services) ── única fuente de verdad para reglas de negocio
   │     ├─ CalculoVentaService      → subtotal/IGV/total (bcmath)
   │     ├─ CorrelativoService       → numeración única bajo concurrencia
   │     ├─ MovimientoCajaService    → creación de movimientos de caja
   │     └─ AuditoriaService         → registro de trazabilidad
   ▼
Eloquent ORM ── persistencia y acceso a datos
   ▼
MySQL/MariaDB ── integridad referencial, constraints, DECIMAL para dinero
```

Ninguna capa asume responsabilidades de otra: Blade no calcula totales, TypeScript no valida reglas de negocio de forma autoritativa, los Controllers no contienen aritmética financiera.

## Principio de cálculo financiero

Todo cálculo financiero crítico (subtotal, IGV, total, saldos) se resuelve **exclusivamente en Laravel**, usando `bcmath` para evitar errores de precisión de punto flotante. El frontend (TypeScript) puede mostrar un cálculo visual idéntico para UX (ej. `resources/ts/modules/venta-form.ts`), pero el backend nunca confía en un valor enviado por el navegador — siempre recalcula.

## Módulos y su relación

```
Cliente ──< Venta ──< DetalleVenta
   │           │
   │           ├──< Comprobante (boleta, serie+correlativo únicos)
   │           └──< CuentaPorCobrar ──< PagoCliente ──> MovimientoCaja (ingreso)
   │
Proveedor ──< CuentaPorPagar ──< PagoProveedor ──> MovimientoCaja (egreso)

Auditoria (polimórfica) ← registra cada escritura de los módulos anteriores
```

Puntos de diseño deliberados:

- **Venta ≠ Ingreso de caja.** Una venta nunca genera un `MovimientoCaja` por sí sola. Solo un `PagoCliente`/`PagoProveedor` real lo genera, dentro de la misma transacción que registra el pago.
- **CuentaPorCobrar/CuentaPorPagar no son automáticas.** Se generan mediante una acción explícita desde la venta ("Generar cuenta por cobrar"), nunca implícitamente.
- **Ningún registro financiero se edita o borra.** Los flujos son de una sola dirección: `registrada → anulada`, `pendiente → parcial → pagada`, `emitido → anulado`. La corrección se hace mediante nuevas operaciones, nunca reescribiendo el pasado.
- **Integridad entre módulos dependientes.** Una `Venta` con boleta emitida o cuenta por cobrar activa no puede anularse hasta que esos registros dependientes se anulen primero (`VentaController::anular`).

## Autenticación y autorización

- **Autenticación:** Laravel Breeze (stack Blade), elegido sobre Fortify (headless, exige construir toda la UI) y Jetstream (equipos/tokens API, fuera de alcance). Hash de contraseñas vía `Hash::` (bcrypt), rate limiting nativo en login (5 intentos por email+IP antes de bloqueo temporal).
- **Autorización:** Policies de Laravel + permisos de spatie/laravel-permission. Cada permiso sigue el patrón `modulo.accion` (ej. `clientes.crear`, `cuentas_cobrar.pagar`). El rol ADMINISTRADOR tiene bypass total vía `Gate::before` en `AppServiceProvider`. Ningún módulo se protege solo desde Blade: cada acción del backend vuelve a verificar el permiso, incluso si el botón correspondiente está oculto en la UI.

## Roles implementados

| Rol | Alcance |
|---|---|
| ADMINISTRADOR | Acceso completo (bypass de todas las verificaciones de permiso) |
| ADMINISTRATIVO | Gestión operativa: clientes, proveedores, ventas, CxC, CxP, caja (solo lectura), reportes, boletas |
| CAJERO | Ventas, cobros a clientes, caja (lectura y registro), boletas — sin acceso a cuentas por pagar, reportes ni auditoría |
| CONTABILIDAD | Lectura de todos los módulos financieros + auditoría, sin capacidad de escritura |

## Extensibilidad prevista

El esquema y la arquitectura están preparados para crecer sin reescritura:

- `comprobantes.tipo` es un enum extensible (hoy solo `boleta`; admite `factura`, `nota_credito` a futuro).
- `Correlativo` es un mecanismo de numeración genérico (clave string), reutilizable para cualquier serie futura.
- `movimientos_caja.origen` es polimórfico — cualquier módulo futuro puede generar movimientos de caja sin cambiar el esquema.
- La capa de permisos permite agregar roles/permisos sin tocar código (spatie/laravel-permission).
