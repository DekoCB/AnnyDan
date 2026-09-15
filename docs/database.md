# Base de datos — AnnyDan

Motor: MySQL/MariaDB. Todos los valores monetarios usan `DECIMAL(12,2)` — nunca `FLOAT`/`DOUBLE` — para evitar errores de precisión financiera.

## Tablas de dominio

| Tabla | Propósito | Claves foráneas | Restricciones destacadas |
|---|---|---|---|
| `clientes` | Clientes de la empresa | — | `UNIQUE(tipo_documento, numero_documento)`, soft delete, índice en `nombre_razon_social` |
| `proveedores` | Proveedores | — | Igual estructura que `clientes` |
| `categorias_movimiento` | Catálogo fijo de categorías de caja | — | `UNIQUE(nombre, tipo)` |
| `ventas` | Encabezado de venta | `cliente_id→clientes`, `user_id→users` | `numero_operacion` único, índices en `fecha` y `estado` |
| `detalle_ventas` | Líneas de una venta | `venta_id→ventas` (cascade) | — |
| `comprobantes` | Boletas emitidas | `venta_id→ventas` | `UNIQUE(tipo, serie, numero_correlativo)` |
| `cuentas_por_cobrar` | Deuda de clientes | `cliente_id→clientes`, `venta_id→ventas` (nullable) | Índices en `estado`, `fecha_vencimiento` |
| `pagos_clientes` | Historial de cobros | `cuenta_por_cobrar_id`, `user_id` | Índice en `fecha_pago` |
| `cuentas_por_pagar` | Deuda a proveedores | `proveedor_id→proveedores` | Índices en `estado`, `fecha_vencimiento` |
| `pagos_proveedores` | Historial de pagos a proveedor | `cuenta_por_pagar_id`, `user_id` | Índice en `fecha_pago` |
| `movimientos_caja` | Ingresos/egresos reales | `categoria_id`, `user_id`, `origen` (polimórfico) | Índices `(tipo, fecha)` y `fecha` |
| `auditorias` | Trazabilidad de operaciones | `user_id` (nullable), `auditable` (polimórfico) | Sin `updated_at` (inmutable), índices en `accion` y `created_at` |
| `correlativos` | Numeración segura por clave | — | `clave` única, incrementado con `lockForUpdate()` |

Tablas de infraestructura: `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs` (núcleo de Laravel) y `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` (spatie/laravel-permission).

## Decisiones de modelado relevantes

- **`saldo` no se almacena.** En `CuentaPorCobrar`/`CuentaPorPagar`, el saldo es un accessor calculado (`monto_total - monto_pagado` vía `bcsub`), nunca una columna persistida — evita desincronización.
- **"Vencida" no se persiste en `estado`.** Se calcula al vuelo (`fecha_vencimiento` pasada + saldo pendiente + no pagada/anulada) mediante el accessor `estado_mostrado` y el scope `scopeVencidas()`, evitando la necesidad de un job programado solo para mantener el estado sincronizado.
- **`venta_id` en `cuentas_por_cobrar` es nullable.** Una cuenta por cobrar puede generarse desde una venta o de forma independiente (sección 7 del requerimiento: "cuando corresponda").
- **`movimientos_caja.origen` es polimórfico.** Permite vincular un movimiento a su `PagoCliente`/`PagoProveedor` de origen sin acoplar el esquema a esos dos tipos específicamente.
- **Ningún borrado físico de datos financieros.** Los `ON DELETE` son `RESTRICT` (o `SET NULL` solo en `auditorias.user_id`) — nunca `CASCADE` sobre datos financieros. La única cascada real es `detalle_ventas` al borrar su `venta` padre (líneas sin existencia propia).
- **Enums en vez de tablas de catálogo** para `tipo_documento`, `forma_pago`, `medio_pago`, `estado` — MVP consciente de su alcance; MariaDB 10.4 valida el enum a nivel de motor.

## Gotchas encontrados durante la implementación

- **Pluralización en español.** El inferidor de nombres de tabla/ruta de Laravel asume inglés: `Proveedor` → `proveedors` (no `proveedores`), `CategoriaMovimiento` → `categoria_movimientos` (no `categorias_movimiento`), etc. Todos los modelos con este problema declaran `protected $table` explícito, y las rutas de recurso usan `->parameters([...])` donde aplica.
- **Columna `estado` repetida.** Existe en casi todas las tablas de dominio. Cualquier `JOIN` entre dos de ellas sin calificar la columna (`ventas.estado` en vez de `estado`) produce `SQLSTATE... ambiguous column name` — ocurrió una vez en `ReporteVentaController` y quedó corregido.

## Migraciones

16 migraciones de dominio (ver `database/migrations/`), aplicadas en orden de dependencia. La migración `2026_09_15_050016_add_performance_indexes_...` fue agregada en la Fase 13 tras medir patrones de consulta reales (ver `docs/testing.md` y el informe final, sección de rendimiento).
