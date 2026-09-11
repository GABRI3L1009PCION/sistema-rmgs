# Flujo Git del proyecto

## Ramas

- `main`: version estable y demostrable. Solo debe recibir cambios probados.
- `develop`: integracion de avances antes de pasar a `main`.
- ramas de trabajo: cada integrante debe trabajar en una rama propia o de funcionalidad antes de integrar cambios.

## Nombres de ramas

Usar nombres cortos, claros y sin espacios.

Ejemplos:

```text
feature/dashboard-reportes
feature/crud-generacion
fix/alertas-desviacion
docs/manual-usuario
```

## Regla de trabajo

1. Crear o actualizar una rama de trabajo desde `develop`.
2. Hacer commits pequenos y descriptivos.
3. Actualizar `CHANGELOG.md` con el cambio realizado.
4. Actualizar `PROMPTS.md` cuando se use IA para analisis, diseno, codigo, pruebas o documentacion.
5. Probar con `php artisan test` antes de subir cambios.
6. Subir la rama al remoto.
7. Integrar a `develop` mediante Pull Request o merge revisado.
8. Pasar a `main` solo cuando la demo este estable y probada.

## Comandos recomendados

Crear una rama desde `develop`:

```bash
git switch develop
git pull origin develop
git switch -c feature/nombre-del-cambio
```

Guardar avances:

```bash
git status
git add .
git commit -m "tipo: descripcion breve"
git push -u origin feature/nombre-del-cambio
```

Actualizar una rama de trabajo con lo ultimo de `develop`:

```bash
git switch feature/nombre-del-cambio
git fetch origin
git merge origin/develop
```

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
- Pull Request o merge hacia `develop` cuando el cambio este listo.

## Normas antes de subir cambios

- No subir secretos, claves API, credenciales ni archivos `.env`.
- No subir codigo que no ejecute localmente.
- No mezclar cambios no relacionados en un mismo commit.
- No modificar `main` directamente durante el desarrollo.
- No borrar historial remoto sin acuerdo del equipo.
- Revisar `git status` antes de cada commit y antes de cada push.
- Mantener mensajes de commit en espanol, claros y en modo descriptivo.
