# Correccion visual de modo oscuro en vistas internas

## Objetivo

Corregir las partes de las vistas que no se adaptaban bien al modo oscuro.

## Motivo

Al activar el tema oscuro algunas secciones seguian usando fondos blancos, encabezados claros o colores fijos de graficas. Eso reducia la legibilidad y rompia la estetica del sistema.

## Cambios realizados

- Se agregaron reglas especificas para modo oscuro en tarjetas de reportes, escenarios, mapa y alertas.
- Se corrigieron fondos de tablas, paneles, ayudas informativas y estados de prioridad.
- Se ajustaron bordes y textos secundarios para mantener contraste.
- Se actualizaron las graficas de reportes y proyecciones para tomar colores desde variables CSS del tema.
- Se ajusto el mapa para que los controles, popups y tiles no choquen visualmente con el fondo oscuro.

## Archivos tocados

- `resources/views/layouts/app.blade.php`
- `resources/views/reports/index.blade.php`
- `resources/views/projections/index.blade.php`
- `CHANGELOG.md`
- `docs/cambios/2026-09-12-correccion-modo-oscuro-vistas.md`

## Verificacion sugerida

```bash
php artisan test
```

Tambien revisar manualmente en modo oscuro:

- Reportes
- Alertas
- Proyecciones
- Mapa
