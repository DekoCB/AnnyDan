# Testing — AnnyDan

Framework: PHPUnit 11.5.56 (no Pest — así lo instaló el scaffolding inicial de Laravel 12, sin `--pest`). Base de datos de pruebas: SQLite en memoria (`phpunit.xml`), aislada de la base de datos de desarrollo/producción.

## Estado actual (medido, no estimado)

```
127 tests, 348 assertions — 100% passing
```

Ejecutado por última vez el 2026-09-15 con `php artisan test`. 23 archivos de test: 21 Feature + 2 Unit.

## Organización

| Archivo | Qué cubre |
|---|---|
| `ClienteTest`, `ProveedorTest` | CRUD, validación de documento por tipo, desactivación sin borrado, permisos por rol |
| `VentaTest` | Cálculo de totales en backend, numeración correlativa, anulación y sus guardas de integridad |
| `CuentaPorCobrarTest`, `CuentaPorPagarTest` | Criterios de aceptación AC-01 a AC-04 del requerimiento (sección 27), literalmente como nombres de test |
| `MovimientoCajaTest` | Vínculo automático pago→movimiento de caja, regla venta≠ingreso |
| `ComprobanteTest` | Numeración serie+correlativo, un comprobante por venta, ciclo emitido→anulado |
| `DashboardTest`, `ReporteVentaTest` | Visibilidad adaptativa por permiso, agregados correctos |
| `AuditoriaTest` | Filtros, inmutabilidad (405 en PATCH/PUT/DELETE) |
| `SecurityTest` | Las 5 categorías de la sección 26 del requerimiento, consolidadas |
| `BusinessFlowRegressionTest` | Un flujo real de extremo a extremo (cliente→venta→CxC→pago→caja→boleta) |
| `PerformanceTest` | Techos de cantidad de consultas SQL por página (guarda anti-N+1) |
| `CuentaPorCobrarCalculoTest` (Unit) | `saldo` y `estaVencida()` en aislamiento, sin HTTP |
| `Auth/*` (Breeze, sin modificar) | Registro, login, reseteo de contraseña, verificación de email |

## Criterios de aceptación verificados literalmente

Los AC-01 a AC-05 de la sección 27 del requerimiento están implementados como tests con ese nombre exacto:

- **AC-01** — crear una cuenta por cobrar registra el monto total correctamente.
- **AC-02** — saldo 1000, pago 300 → saldo 700.
- **AC-03** — saldo 700, intento de pago 800 → rechazado (verificado en dos capas: atributo `max` del navegador y regla de validación del servidor).
- **AC-04** — saldo llega a 0 → estado cambia a PAGADA.
- **AC-05** — cuenta vencida se identifica como tal (test unitario sobre `estaVencida()`).

## Calidad de código, ejecutada en cada fase

- **Pint** (PSR-12 + convenciones Laravel): `passed` en la última corrida.
- **Larastan/PHPStan nivel 5**: `[OK] No errors` en la última corrida (53 archivos analizados en `app/`).
- **TypeScript estricto** (`tsc`, sin `noEmit` errors) y `vite build`: sin errores.
- **`composer audit` / `npm audit`**: 0 advisories.

## Lo que no está cubierto (honesto, no inventado)

- **Cobertura de código (%)**: no medida — no hay driver de cobertura (xdebug/pcov) instalado en este entorno. No se reporta un número.
- **Pruebas de UI automatizadas (Playwright/Dusk) como parte del suite permanente**: la verificación visual se hizo manualmente con Playwright durante el desarrollo de cada módulo (capturas de pantalla, sin errores de consola), pero no quedó como suite ejecutable en el repositorio.
- **Pruebas de carga/concurrencia real** (múltiples procesos escribiendo simultáneamente): el mecanismo de `CorrelativoService` está diseñado para ser seguro bajo concurrencia (`lockForUpdate()` dentro de transacción) pero no se ejecutó una prueba de concurrencia real con procesos paralelos — sería el siguiente paso recomendado antes de un despliegue con múltiples cajeros simultáneos.

## Cómo ejecutar

```bash
php artisan test              # suite completa
php artisan test --filter=X   # un archivo/test específico
./vendor/bin/pint              # estilo
./vendor/bin/phpstan analyse   # análisis estático
npm run type-check             # TypeScript
npm run build                  # build de producción
```
