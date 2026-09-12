# Manual breve de usuario

## Dashboard

Entrar a `/` para revisar los indicadores nacionales, la grafica de generacion real contra esperada, el mapa interactivo, ranking por departamento, alertas activas, granjas registradas e historial de generacion.

## Registrar granja solar

1. Seleccionar `Nueva granja`.
2. Completar nombre, responsable, departamento, municipio, latitud, longitud y familias beneficiadas.
3. Seleccionar modelo de panel y cantidad instalada.
4. Ingresar generacion esperada y real inicial.
5. Guardar. El sistema calcula CO2 evitado y crea alerta si aplica.

## Editar o desactivar granja

1. En la tabla `Granjas registradas y proyeccion`, seleccionar `Editar`.
2. Actualizar datos generales, ubicacion, familias o estado.
3. Guardar cambios.
4. Para desactivar sin borrar historial, seleccionar `Desactivar`.

## Registrar generacion mensual

1. Seleccionar `Nueva generacion`.
2. Elegir granja solar y periodo.
3. Ingresar generacion esperada y generacion real.
4. Guardar. El sistema calcula `generacion real * 0.40` para CO2 evitado y genera alerta si la desviacion es de 20% o mas.

## Editar o eliminar generacion historica

1. En `Historial de generacion`, seleccionar `Editar` para corregir periodo, granja o valores.
2. Guardar para recalcular CO2 y alertas.
3. Seleccionar `Eliminar` solo cuando el registro fue ingresado por error.

## API

Consultar `/api/docs` para ver endpoints disponibles y reglas de calculo.
