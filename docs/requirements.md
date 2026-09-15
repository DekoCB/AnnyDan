# Trazabilidad de requerimientos — AnnyDan

Mapeo de cada módulo pedido en el documento de requerimiento original (`ANNYDAN.txt`) contra lo efectivamente implementado. Estado basado en evidencia (tests + verificación visual), no en intención.

| # | Módulo requerido (sección del documento) | Implementado | Evidencia |
|---|---|---|---|
| 5 | Clientes | ✅ Completo | `ClienteController`, `ClienteTest` (8 tests) |
| 6 | Proveedores | ✅ Completo | `ProveedorController`, `ProveedorTest` (8 tests) |
| 7 | Cuentas por cobrar | ✅ Completo | `CuentaPorCobrarController`, AC-01 a AC-04 verificados |
| 8 | Cuentas por pagar | ✅ Completo | `CuentaPorPagarController`, mismo patrón que CxC |
| 9 | Ventas | ✅ Completo | `VentaController`, cálculo backend verificado |
| 10 | Resumen de ventas mensuales | ✅ Completo | `ReporteVentaController` — mes/año, por cliente, por forma de pago, comparación con periodo anterior |
| 11 | Flujo de caja | ✅ Completo | `MovimientoCajaController`, vínculo automático con pagos, regla venta≠ingreso |
| 12 | Boleta de venta | ✅ Completo (sin integración SUNAT real, por diseño) | `ComprobanteController`, numeración serie+correlativo segura |
| 13 | Dashboard principal | ✅ Completo | `DashboardController`, adaptativo por permiso |
| 14 | Seguridad | ✅ Completo | Ver `docs/security.md` |
| 15 | Auditoría y trazabilidad | ✅ Completo | `AuditoriaService` + UI de consulta de solo lectura |
| 16 | Integridad financiera | ✅ Completo | Transacciones DB en toda operación multi-paso |
| 17 | Manejo monetario | ✅ Completo | `DECIMAL(12,2)` + `bcmath` centralizado |
| 22 | Flexibilidad/escalabilidad | ✅ Prevista, no implementada | Ver `docs/architecture.md`, sección de extensibilidad |
| 26 | Testing | ✅ Completo | 127 tests, ver `docs/testing.md` |
| 27 | Criterios de aceptación | ✅ Verificados literalmente | AC-01 a AC-05 como nombres de test |
| 30 | Documentación | ✅ Este conjunto de documentos |
| 31 | Matriz ISO/IEC 25010 | ✅ `docs/iso-25010.md` |

## Explícitamente fuera de alcance de este MVP (por instrucción directa del requerimiento)

- Facturación electrónica SUNAT real (sección 12: "No implementes una integración tributaria real sin los requisitos y credenciales correspondientes").
- Facturas, notas de crédito/débito, productos, inventario, compras, gastos como módulo propio, multiempresa, multiusuario avanzado, integraciones externas (sección 22/23: listadas como escalabilidad futura, no como MVP).

## Decisiones de diseño no explícitas en el documento, tomadas y justificadas

Estas decisiones no estaban especificadas literalmente y se resolvieron con criterio de ingeniería, documentadas en cada punto para que puedan revisarse:

1. **Roles/permisos**: spatie/laravel-permission (autorizado explícitamente por el usuario en la Fase 1).
2. **Cálculo monetario**: DECIMAL + Service nativo con bcmath, no una librería externa (autorizado explícitamente en la Fase 1).
3. **Autenticación**: Laravel Breeze, stack Blade (justificado en `docs/architecture.md`).
4. **CuentaPorCobrar/Comprobante como acción manual**, no automática al crear una venta — interpretación de la frase "cuando corresponda" del requerimiento (sección 7).
5. **Un solo movimiento de caja por pago**, nunca por venta — interpretación directa y literal de la advertencia explícita "VENTA ≠ INGRESO DE CAJA" (sección 11).
