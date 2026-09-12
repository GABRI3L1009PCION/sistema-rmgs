# Servicio de reglas solares

## Objetivo

Centralizar las reglas de calculo solar en una clase de servicio para que el sistema use una sola fuente de verdad.

## Motivo

Los calculos de capacidad instalada, CO2 evitado, desviacion, alerta y proyeccion estaban repartidos entre modelos, controladores y seeders. Centralizarlos mejora el mantenimiento, reduce duplicacion y facilita agregar pruebas.

## Que se hizo

- Se creo `SolarMetricsService`.
- Se definieron constantes para el factor de CO2, umbral de alerta y periodos de proyeccion.
- Se movieron al servicio los calculos de:
  - capacidad instalada;
  - CO2 evitado;
  - porcentaje de desviacion;
  - activacion de alerta;
  - proyeccion por promedio movil.
- Se actualizaron modelos, controlador, seeder y documentacion de API para usar el servicio.

## Archivos tocados

- `app/Services/SolarMetricsService.php`
- `app/Models/SolarFarm.php`
- `app/Models/EnergyRecord.php`
- `app/Http/Controllers/SolarFarmController.php`
- `app/Http/Controllers/ApiController.php`
- `database/seeders/DatabaseSeeder.php`
- `docs/cambios/2026-09-11-servicio-reglas-solares.md`

## Como probar

1. Ejecutar `php artisan test`.
2. Ejecutar `php artisan migrate:fresh --seed`.
3. Abrir el dashboard y verificar que los indicadores sigan cargando.
4. Revisar `/api/docs` para confirmar que la API menciona el servicio de reglas.

## Resultado de pruebas

```text
php artisan migrate:fresh --seed -> correcto
php artisan test -> 2 passed
```

## Pendientes

- Agregar pruebas unitarias especificas para `SolarMetricsService`.
