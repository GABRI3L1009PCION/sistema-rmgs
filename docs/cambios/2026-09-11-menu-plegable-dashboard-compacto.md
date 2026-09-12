# Menu plegable y dashboard compacto

## Objetivo

Mejorar la ergonomia visual del sistema permitiendo ocultar o mostrar el menu lateral y ajustar el dashboard para que pueda verse completo sin desplazamiento vertical en escritorio.

## Motivo

El dashboard ocupaba mas altura de la necesaria y obligaba a hacer scroll para ver secciones importantes. Tambien se necesitaba aprovechar mejor el espacio horizontal cuando el menu lateral no fuera necesario.

## Cambios realizados

- Se agrego un boton para ocultar y mostrar el menu lateral.
- Se ajusto el boton como control flotante visible entre el menu y el contenido principal.
- El estado del menu se guarda en `localStorage` para conservar la preferencia del usuario.
- Al colapsarse, el menu muestra solo iconos y conserva tooltips por titulo.
- El dashboard se compacto en tres franjas:
  - resumen principal y KPIs;
  - grafica y lectura rapida;
  - top de granjas y alertas principales.
- Se redujo el resumen inferior del dashboard a las 3 granjas principales y 2 alertas principales para evitar scroll.
- En pantallas pequenas el dashboard vuelve a permitir scroll para mantener legibilidad.

## Archivos tocados

- `resources/views/layouts/app.blade.php`
- `resources/views/dashboard.blade.php`
- `CHANGELOG.md`

## Pruebas realizadas

```bash
php artisan test
```

Tambien se validaron rutas principales con servidor local:

```text
/
/granjas
/paneles
/generacion
/reportes
/alertas
/proyecciones
/mapa
/configuracion
```

Resultado: todas respondieron `200`.

## Pendiente

- Revisar visualmente en la resolucion exacta del equipo usado para la presentacion.
