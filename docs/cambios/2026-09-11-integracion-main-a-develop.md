# Integracion de cambios enviados a main

## Objetivo

Actualizar `develop` con los cambios que fueron enviados a `main` por error, manteniendo `develop` como rama principal de integracion durante el desarrollo.

## Motivo

El flujo del equipo indica que las ramas personales o de funcionalidad deben integrarse primero en `develop`. Como algunos cambios fueron enviados directamente a `main`, se integro ese trabajo de regreso a `develop` para mantener la rama de desarrollo actualizada.

## Que se hizo

- Se hizo `fetch` para traer el estado remoto actualizado.
- Se detecto que `origin/main` tenia commits que no estaban en `origin/develop`.
- Se hizo merge de `origin/main` dentro de `develop`.
- Se resolvieron conflictos en:
  - `CHANGELOG.md`;
  - `app/Http/Controllers/SolarFarmController.php`;
  - `database/seeders/DatabaseSeeder.php`;
  - `docs/RUBRICA_CHECKLIST.md`.
- Se conservaron las pantallas CRUD y mejoras funcionales enviadas a `main`.
- Se conservo `SolarMetricsService` como fuente central para los calculos solares.
- Se ajusto el factor de CO2 del servicio a `0.40 kg CO2/kWh`, consistente con los cambios entrantes.
- Se actualizaron las pruebas unitarias del servicio para el nuevo factor de CO2.

## Archivos tocados

- `CHANGELOG.md`
- `app/Http/Controllers/SolarFarmController.php`
- `app/Services/SolarMetricsService.php`
- `database/seeders/DatabaseSeeder.php`
- `docs/RUBRICA_CHECKLIST.md`
- `tests/Unit/SolarMetricsServiceTest.php`
- Archivos funcionales y vistas provenientes de `origin/main`

## Como probar

```bash
php artisan migrate:fresh --seed
php artisan test
php artisan route:list
php artisan serve --host=127.0.0.1 --port=8010
```

Rutas revisadas manualmente:

- `/`
- `/granjas`
- `/generacion`
- `/paneles`
- `/reportes`
- `/mapa`
- `/alertas`
- `/proyecciones`
- `/api/stats`

## Resultado de pruebas

```text
php artisan migrate:fresh --seed -> correcto
php artisan test -> 7 passed
php artisan route:list -> correcto
Rutas revisadas -> 200 OK
```

## Pendientes

- Evitar nuevos cambios directos a `main`.
- Integrar los proximos cambios desde ramas personales hacia `develop`.
- Al final del proyecto, pasar `develop` a `main` cuando la entrega este lista.
