# Paleta visual, modo oscuro y menu de usuario

## Objetivo

Mejorar la estetica general de las vistas internas del sistema y ordenar las acciones de usuario dentro del header.

## Motivo

Las pantallas estaban usando demasiadas superficies blancas y el menu de usuario se sentia pesado. Para una presentacion de concurso, el sistema necesita una identidad visual mas cuidada, mejor contraste y una opcion de modo oscuro.

## Cambios realizados

- Se ajustaron las variables globales de color para una paleta verde, azul y grafito mas sobria.
- Se agrego soporte de modo oscuro mediante `data-theme` en el elemento `html`.
- Se guardo la preferencia del tema en `localStorage`.
- Se agrego un boton de cambio de tema dentro del menu de usuario.
- Se movio el cambio de tema al header como accion rapida junto al usuario.
- Se agrego animacion de transicion al cambiar entre modo claro y modo oscuro.
- Se redisenio el dropdown del usuario para que sea mas compacto.
- El boton de cerrar sesion quedo dentro del dropdown del usuario.
- Se agregaron overrides globales para cards, tablas, inputs, filas, alertas y mapas en modo oscuro.

## Archivos tocados

- `resources/views/layouts/app.blade.php`
- `CHANGELOG.md`
- `docs/cambios/2026-09-12-paleta-modo-oscuro-header.md`

## Verificacion sugerida

```bash
php artisan test
```

Tambien se debe revisar manualmente:

- Abrir el dropdown del usuario.
- Cambiar entre modo claro y oscuro.
- Navegar por dashboard, granjas, paneles, generacion, reportes, alertas, proyecciones y mapa.
- Confirmar que la preferencia visual se mantiene al recargar.
