# Rutas — AnnyDan

Todas las rutas listadas están bajo el grupo `Route::middleware('auth')`, salvo `/`, `/login`, `/register` y las de recuperación de contraseña (Breeze). Ninguna acción de escritura depende solo de que el botón esté oculto en la UI: cada Controller vuelve a autorizar con `$this->authorize(...)` o `authorizeResource(...)`.

No hay una API JSON separada — el sistema es Laravel + Blade + TypeScript (sección 2.11 del requerimiento), sin SPA. Las únicas peticiones asíncronas son envíos de formulario estándar.

## Clientes — permiso base `clientes.*`

| Método | URI | Nombre | Permiso |
|---|---|---|---|
| GET | `/clientes` | `clientes.index` | `clientes.ver` |
| GET | `/clientes/create` | `clientes.create` | `clientes.crear` |
| POST | `/clientes` | `clientes.store` | `clientes.crear` |
| GET | `/clientes/{cliente}` | `clientes.show` | `clientes.ver` |
| GET | `/clientes/{cliente}/edit` | `clientes.edit` | `clientes.editar` |
| PUT/PATCH | `/clientes/{cliente}` | `clientes.update` | `clientes.editar` |
| DELETE | `/clientes/{cliente}` | `clientes.destroy` | `clientes.desactivar` (desactiva, no borra) |
| PATCH | `/clientes/{cliente}/activar` | `clientes.activate` | `clientes.desactivar` |

## Proveedores — mismo patrón que Clientes, permiso base `proveedores.*`

Rutas idénticas en forma bajo `/proveedores`.

## Ventas — permiso base `ventas.*` (no es CRUD estándar: sin edit/update)

| Método | URI | Nombre | Permiso |
|---|---|---|---|
| GET | `/ventas` | `ventas.index` | `ventas.ver` |
| GET | `/ventas/create` | `ventas.create` | `ventas.crear` |
| POST | `/ventas` | `ventas.store` | `ventas.crear` |
| GET | `/ventas/{venta}` | `ventas.show` | `ventas.ver` |
| PATCH | `/ventas/{venta}/anular` | `ventas.anular` | `ventas.anular` |
| POST | `/ventas/{venta}/comprobante` | `comprobantes.store` | `comprobantes.emitir` |

## Cuentas por cobrar — permiso base `cuentas_cobrar.*`

| Método | URI | Nombre | Permiso |
|---|---|---|---|
| GET | `/cuentas-por-cobrar` | `cuentas-cobrar.index` | `cuentas_cobrar.ver` |
| GET | `/cuentas-por-cobrar/create` | `cuentas-cobrar.create` | `cuentas_cobrar.crear` |
| POST | `/cuentas-por-cobrar` | `cuentas-cobrar.store` | `cuentas_cobrar.crear` |
| GET | `/cuentas-por-cobrar/{cuenta_por_cobrar}` | `cuentas-cobrar.show` | `cuentas_cobrar.ver` |
| POST | `/cuentas-por-cobrar/{cuenta_por_cobrar}/pagos` | `cuentas-cobrar.pagar` | `cuentas_cobrar.pagar` |
| PATCH | `/cuentas-por-cobrar/{cuenta_por_cobrar}/anular` | `cuentas-cobrar.anular` | `cuentas_cobrar.crear` |

## Cuentas por pagar — mismo patrón, permiso base `cuentas_pagar.*`, bajo `/cuentas-por-pagar`

## Caja — permiso base `caja.*`

| Método | URI | Nombre | Permiso |
|---|---|---|---|
| GET | `/caja` | `caja.index` | `caja.ver` |
| GET | `/caja/create` | `caja.create` | `caja.registrar` |
| POST | `/caja` | `caja.store` | `caja.registrar` |

## Boletas — permiso `comprobantes.emitir` / `comprobantes.ver`

| Método | URI | Nombre |
|---|---|---|
| GET | `/comprobantes` | `comprobantes.index` |
| GET | `/comprobantes/{comprobante}` | `comprobantes.show` |
| PATCH | `/comprobantes/{comprobante}/anular` | `comprobantes.anular` |

## Reportes y Auditoría — sin Policy, autorización directa por permiso

| Método | URI | Nombre | Permiso |
|---|---|---|---|
| GET | `/reportes/ventas` | `reportes.ventas` | `reportes.ver` |
| GET | `/auditoria` | `auditoria.index` | `auditoria.ver` |
| GET | `/auditoria/{auditoria}` | `auditoria.show` | `auditoria.ver` |

## Dashboard

| Método | URI | Nombre |
|---|---|---|
| GET | `/dashboard` | `dashboard` (adaptativo: cada sección solo se consulta si el usuario tiene el permiso del módulo correspondiente) |

## Autenticación (Breeze, sin modificar)

`/login`, `/register`, `/logout`, `/forgot-password`, `/reset-password/{token}`, `/confirm-password`, `/verify-email/*`, `/profile` (editar perfil propio).
