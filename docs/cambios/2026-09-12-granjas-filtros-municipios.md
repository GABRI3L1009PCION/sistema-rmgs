# Cambio: filtros funcionales de granjas y municipios dependientes

## Objetivo

Hacer que la vista de granjas solares responda correctamente al departamento seleccionado y evitar datos incoherentes entre departamento y municipio.

## Que se cambio

- El filtro de departamento en `Granjas solares` ahora actualiza la tabla y el mapa al mismo tiempo.
- Se agrego un filtro de municipio que se llena automaticamente segun el departamento elegido.
- Los marcadores del mapa se agregan o retiran segun los filtros activos.
- El mapa reajusta su encuadre para mostrar solo las granjas visibles.
- Los formularios de crear y editar granja usan una lista de municipios dependiente del departamento.
- El servidor valida que el municipio enviado pertenezca al departamento elegido.
- Al cambiar departamento o municipio, el formulario actualiza la latitud y longitud sugerida para evitar que el mapa conserve coordenadas de una ubicacion anterior.
- Antes de guardar, el servidor normaliza las coordenadas con la ubicacion del municipio seleccionado.

## Archivos tocados

- `app/Http/Controllers/SolarFarmController.php`
- `resources/views/farms/index.blade.php`
- `resources/views/farms/create.blade.php`
- `resources/views/farms/edit.blade.php`

## Para que sirve

Esto evita que una granja quede registrada con departamento y municipio cruzados, y hace que el mapa de granjas sea una herramienta real de consulta por ubicacion.

Tambien evita el caso donde una granja cambia de `Puerto Barrios` a otro municipio pero mantiene las coordenadas antiguas, que era lo que provocaba que el marcador siguiera apareciendo en Puerto Barrios.

## Pruebas recomendadas

- Entrar a `Granjas solares`.
- Filtrar por un departamento y confirmar que la tabla y el mapa muestran solo sus granjas.
- Elegir un municipio y confirmar que el resultado se reduce correctamente.
- Editar una granja, cambiar el departamento y verificar que la lista de municipios cambia.
- Ejecutar `php artisan test`.
