# Cambio: filtros funcionales en generación

## Objetivo

Hacer que la vista de generación mensual use los registros reales guardados en la base de datos para recalcular métricas, gráficas y tabla al cambiar filtros.

## Alcance

- Se expuso la información de registros históricos hacia JavaScript en la vista `/generacion`.
- Los filtros de periodo, departamento y granja ahora actualizan los indicadores sin recargar la página.
- El filtro de granja se ajusta automáticamente según el departamento seleccionado.
- Las gráficas de tendencia mensual y producción por granja se reconstruyen con los datos filtrados.
- La tabla de registros históricos se sincroniza con los mismos criterios de filtrado.
- Se corrigieron textos visibles de la vista para usar acentos en español.

## Archivos tocados

- `app/Http/Controllers/SolarFarmController.php`
- `resources/views/records/index.blade.php`
- `tests/Feature/ManagementPagesTest.php`

## Pruebas

- `php artisan test`
