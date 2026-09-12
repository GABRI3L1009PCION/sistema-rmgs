# Ajustes en la vista dashboard

## Objetivo

Pulir la vista principal del dashboard para que sea informativa, limpia y consistente con las demas pantallas.

## Cambios realizados

- Se retiro la barra global de busqueda, periodo y acciones porque cada modulo ya tiene sus filtros propios.
- El header superior queda enfocado en el usuario y el control del menu lateral.
- Se ajusto el menu lateral plegable.
- El dashboard quedo solo como vista informativa, sin botones de creacion o registro.
- Se redujo el peso visual de la grafica.
- Se corrigio el espaciado del titulo principal.
- El hero del dashboard ahora ocupa todo el ancho como las demas vistas.
- Los KPIs principales se movieron a una fila separada debajo del hero.

## Archivos tocados

- `resources/views/layouts/app.blade.php`
- `resources/views/dashboard.blade.php`

## Pruebas realizadas

```bash
php artisan test
```

Resultado: pruebas ejecutadas correctamente.
