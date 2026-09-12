# Filtro de periodo funcional en dashboard

## Objetivo

Hacer funcional el selector de mes del dashboard para que deje de ser solo visual.

## Motivo

La grafica del dashboard mostraba un selector de periodo, pero no cambiaba los datos. Esto podia confundir porque parecia un filtro real.

## Cambios realizados

- Se agrego lectura del parametro `period` en la ruta principal del dashboard.
- Se calculan los periodos disponibles desde `energy_records`.
- Se filtran los indicadores de generacion, CO2, promedio diario y resumen nacional segun el mes seleccionado.
- La grafica muestra los ultimos periodos hasta el mes seleccionado.
- El selector de mes ahora es un formulario real que recarga el dashboard al cambiar el periodo.
- Se agrego una prueba para validar que el resumen mensual cambia al usar el filtro.
- Se agrego un endpoint JSON para actualizar grafica y resumen sin recargar la pagina.
- El selector ahora usa `fetch` para refrescar Chart.js y los KPIs en vivo, con fallback al formulario si ocurre un error.
- Se retiraron porcentajes decorativos de KPIs de inventario y se dejo texto descriptivo real.
- Se agrego variacion real de CO2 contra el mes anterior.
- Las alertas recientes y el top de granjas ahora se filtran por el periodo seleccionado.
- La actualizacion en vivo tambien refresca alertas, top de granjas y tendencia de CO2.

## Archivos tocados

- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`
- `routes/web.php`
- `tests/Feature/ManagementPagesTest.php`
- `CHANGELOG.md`
- `docs/cambios/2026-09-12-dashboard-filtro-periodo.md`

## Verificacion

```bash
php artisan test
```

Resultado esperado: todas las pruebas pasan y el dashboard responde a `/?period=YYYY-MM`.
