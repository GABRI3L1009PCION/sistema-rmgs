# Cambio: acceso visible para registrar granjas

## Objetivo

Hacer visible el flujo de registro de nuevas granjas solares desde la pantalla de `Granjas solares`.

## Que se cambio

- Se agrego el boton `Nueva granja` en el encabezado del listado de granjas.
- El boton apunta a la ruta existente `/granjas/nueva`.

## Archivos tocados

- `resources/views/farms/index.blade.php`

## Para que sirve

El sistema ya tenia el formulario de creacion, pero el usuario no tenia un acceso claro desde la vista principal de granjas. Con este cambio el flujo de registro queda disponible de forma directa.

## Pruebas recomendadas

- Entrar a `/granjas`.
- Presionar `Nueva granja`.
- Confirmar que carga el formulario de registro.
