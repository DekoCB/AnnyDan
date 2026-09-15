# Instalación y despliegue — AnnyDan

## Requisitos verificados en este entorno

- PHP 8.2.12 (con extensiones estándar de XAMPP)
- Composer 2.8.3
- Node.js 22.18.0 / npm 10.8.3
- MySQL/MariaDB 10.4.32

## Instalación local (desarrollo)

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Crear la base de datos (ejemplo con el cliente de MySQL/MariaDB de XAMPP)
mysql -u root -e "CREATE DATABASE annydan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
npm run build
```

El seeder crea: los 4 roles con su matriz de permisos, las 5 categorías fijas de movimiento de caja, y un usuario administrador de desarrollo (`admin@annydan.test` / `password`) — **cambiar esta contraseña o eliminar el usuario antes de cualquier uso real**.

## Ejecutar en desarrollo

```bash
php artisan serve        # servidor de desarrollo en http://127.0.0.1:8000
npm run dev               # Vite con hot reload, en paralelo
# o, todo junto:
composer run dev
```

**Importante:** `APP_URL` no debe incluir una subruta (ej. `http://localhost/AnnyDan/public`) — Laravel usa ese valor para construir URLs internas y para el cliente HTTP de los tests, y una subruta rompe ambos. Usar `php artisan serve`, no navegar directamente a la carpeta del proyecto dentro del `htdocs` de XAMPP/Apache.

## Variables de entorno propias del proyecto

| Variable | Propósito | Valor por defecto |
|---|---|---|
| `TASA_IGV` | Tasa de IGV aplicada a toda venta | `0.18` |
| `SERIE_BOLETA` | Serie única usada al emitir boletas | `B001` |

## Preparación para producción (verificado, no solo documentado)

```bash
composer install --no-dev --optimize-autoloader
npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Los tres comandos de cacheo se ejecutaron y se revirtieron exitosamente durante la Fase 13 (ver `docs/testing.md` / informe final) — confirmado que la aplicación es compatible con ellos, incluida la ruta raíz basada en closure.

**No dejar estos cachés activos en desarrollo** — un cambio de código no se refleja hasta correr `route:clear`/`config:clear`/`view:clear`.

## Recomendaciones no implementadas para producción real

- Usuario de base de datos dedicado (no `root`) sin permisos `DROP`/`ALTER` sobre `auditorias`.
- `APP_DEBUG=false` y `APP_ENV=production`.
- HTTPS obligatorio (`SESSION_SECURE_COOKIE=true`).
- Backups automáticos de la base de datos.
- Servidor web real (Apache/Nginx + PHP-FPM) en vez de `artisan serve`.
- Cierre o conversión a "alta por invitación" de la ruta pública `/register` (ver `docs/security.md`).
