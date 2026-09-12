# Retiro del apartado de parametros

## Objetivo

Retirar el apartado de parametros/configuracion del sistema para mantener la aplicacion enfocada en las funciones centrales del reto.

## Motivo

La pantalla de parametros podia percibirse como una seccion adicional poco necesaria para la demo. Las reglas importantes del sistema ya estan documentadas en el README y en los documentos tecnicos, por lo que no era indispensable mantener una vista propia.

## Cambios realizados

- Se retiro el enlace de parametros del menu lateral.
- Se eliminaron las rutas `/configuracion`.
- Se quitaron los metodos del controlador asociados a parametros.
- Se elimino la vista `resources/views/settings/index.blade.php`.
- Se conserva la documentacion tecnica de reglas solares fuera de la interfaz.

## Archivos tocados

- `routes/web.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/settings/index.blade.php`
- `CHANGELOG.md`

## Pruebas realizadas

```bash
php artisan test
```

## Resultado

El sistema queda mas enfocado para la presentacion: dashboard, granjas, paneles, generacion, reportes, alertas, proyecciones y mapa.
