# Pruebas de reglas solares

## Objetivo

Agregar pruebas unitarias para validar los calculos criticos del sistema solar.

## Motivo

La funcionalidad del reto depende de reglas de negocio que deben ser confiables: capacidad instalada, CO2 evitado, desviaciones, alertas y proyecciones. Probar estas reglas reduce riesgo durante la demo y ayuda a demostrar calidad tecnica.

## Que se hizo

- Se agrego `SolarMetricsServiceTest`.
- Se probaron los calculos de:
  - CO2 evitado;
  - porcentaje de desviacion;
  - activacion de alerta al 20%;
  - proyeccion con los ultimos 3 periodos;
  - capacidad instalada por cantidad y potencia de paneles.

## Archivos tocados

- `tests/Unit/SolarMetricsServiceTest.php`
- `docs/cambios/2026-09-11-pruebas-reglas-solares.md`
- `CHANGELOG.md`

## Como probar

Ejecutar:

```bash
php artisan test
```

## Resultado de pruebas

```text
php artisan test -> 7 passed
```

## Pendientes

- Agregar pruebas de integracion para el formulario de granjas cuando el CRUD se complete.
