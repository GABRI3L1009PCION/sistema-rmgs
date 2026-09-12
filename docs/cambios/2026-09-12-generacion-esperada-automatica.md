# Cambio: generacion esperada sugerida automaticamente

## Objetivo

Evitar que la generacion esperada de una granja solar parezca un numero inventado y hacer que el formulario proponga un valor tecnico basico.

## Que se cambio

- El formulario de nueva granja calcula la capacidad instalada estimada segun modelo de panel y cantidad.
- La generacion esperada mensual se sugiere automaticamente con la formula interna:

```text
capacidad instalada kW x 5 horas solares pico x 30 dias x 0.80
```

- La generacion esperada queda editable por si el operador tiene un estudio tecnico mas preciso, sin mostrar explicaciones adicionales en la interfaz.
- La generacion real queda como dato manual, porque representa una lectura observada del periodo.

## Archivos tocados

- `resources/views/farms/create.blade.php`

## Para que sirve

El registro de granjas queda mas defendible ante jurado: el sistema calcula una expectativa razonable y el usuario solo coloca la lectura real para comparar rendimiento.

## Pruebas recomendadas

- Entrar a `/granjas/nueva`.
- Cambiar modelo de panel y cantidad de paneles.
- Confirmar que capacidad estimada y generacion esperada cambian automaticamente.
- Editar manualmente la generacion esperada si se desea usar un dato tecnico propio.
