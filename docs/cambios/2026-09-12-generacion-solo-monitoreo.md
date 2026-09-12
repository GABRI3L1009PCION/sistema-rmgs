# Cambio: generación como vista de monitoreo

## Objetivo

Evitar que la pantalla de generación mensual parezca un módulo de carga manual de lecturas. La vista queda enfocada en consultar valores reales ya existentes en la base de datos.

## Alcance

- Se retiró el botón de nueva lectura de la pantalla `/generacion`.
- Se retiraron las acciones de edición y eliminación de la tabla de registros históricos.
- Se retiraron las rutas web de creación, edición y eliminación manual de lecturas.
- El selector de periodo inicia en `Todos los periodos` para mostrar el panorama completo.
- Las métricas iniciales de generación se calculan con todos los registros disponibles.
- La tabla conserva la columna de periodo para que cada lectura mantenga su contexto mensual.

## Archivos tocados

- `app/Http/Controllers/SolarFarmController.php`
- `resources/views/records/index.blade.php`
- `routes/web.php`
- `tests/Feature/ManagementPagesTest.php`

## Pruebas

- `php artisan test`
