# Norma de documentacion por cambio

## Objetivo

Establecer que cada cambio solicitado en el proyecto debe quedar explicado en un archivo Markdown.

## Motivo

La competencia evalua documentacion, claridad del proceso, uso de IA y control del desarrollo. Registrar cada cambio ayuda a demostrar trabajo incremental, facilita la revision del equipo y permite explicar decisiones durante la presentacion.

## Que se hizo

- Se actualizo `docs/FLUJO_GIT.md` para agregar la seccion de documentacion por cambio.
- Se definio que los documentos deben guardarse en `docs/cambios/`.
- Se agrego una plantilla minima para explicar cada cambio.
- Se actualizo `docs/RUBRICA_CHECKLIST.md` para marcar esta regla como parte de la evidencia.
- Se actualizo `CHANGELOG.md` para registrar la nueva norma.

## Archivos tocados

- `docs/FLUJO_GIT.md`
- `docs/RUBRICA_CHECKLIST.md`
- `CHANGELOG.md`
- `docs/cambios/2026-09-11-norma-documentacion-cambios.md`

## Como probar

Este cambio es documental. Para verificarlo:

1. Revisar que exista la carpeta `docs/cambios/`.
2. Revisar que la regla este descrita en `docs/FLUJO_GIT.md`.
3. Confirmar que el checklist incluya la norma.
4. Ejecutar las pruebas del proyecto.

## Resultado de pruebas

```text
php artisan test -> 2 passed
```

## Pendientes

- Crear documentos similares para los siguientes cambios funcionales.
- Mantener esta practica en cada rama de trabajo antes de integrar a `develop`.
