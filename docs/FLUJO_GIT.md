# Flujo Git del proyecto

## Ramas

- `main`: version estable y demostrable. Solo debe recibir cambios probados.
- `develop`: integracion de avances antes de pasar a `main`.
- `sandr`: rama personal de trabajo. Los cambios se hacen aqui primero.

## Regla de trabajo

1. Trabajar en `sandr`.
2. Hacer commits pequenos y descriptivos.
3. Actualizar `CHANGELOG.md` y `PROMPTS.md` cuando aplique.
4. Probar con `php artisan test`.
5. Integrar a `develop`.
6. Pasar a `main` solo cuando la demo este estable.

## Formato de commits sugerido

```text
tipo: descripcion breve
```

Tipos:

- `feat`: funcionalidad nueva.
- `fix`: correccion.
- `docs`: documentacion.
- `test`: pruebas.
- `refactor`: mejora interna sin cambiar comportamiento.
- `chore`: configuracion o mantenimiento.

Ejemplos:

```text
feat: agregar registro de generacion mensual
docs: documentar endpoints de la API REST
fix: corregir calculo de desviacion para alertas
```

## Evidencia para la rubrica

Cada bloque importante debe dejar:

- Commit claro.
- Actualizacion en `CHANGELOG.md`.
- Prompt o resumen en `PROMPTS.md` si se uso IA.
- Verificacion ejecutada.
