# Flujo Git del proyecto

## Ramas

- `main`: version estable y demostrable. Solo debe recibir cambios probados.
- `develop`: integracion de avances antes de pasar a `main`.
- ramas de trabajo: cada integrante debe trabajar en una rama propia o de funcionalidad antes de integrar cambios.

## Regla principal

Durante el desarrollo, todos los cambios deben salir desde la rama propia de cada integrante hacia `develop`.

`main` no debe recibir cambios directos ni merges parciales. Solo se actualizara al final, cuando el proyecto este terminado, probado y listo para la entrega o demostracion oficial.

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
2. Hacer commits pequeños y descriptivos.
3. Actualizar `CHANGELOG.md` con el cambio realizado.
4. Actualizar `PROMPTS.md` cuando se use IA para analisis, diseño, codigo, pruebas o documentacion.
5. Probar con `php artisan test` antes de subir cambios.
6. Subir la rama al remoto.
7. Integrar la rama de trabajo a `develop` mediante Pull Request o merge revisado.
8. Mantener `main` sin cambios durante el desarrollo.
9. Pasar `develop` a `main` solo cuando el proyecto este terminado, estable y probado para entrega.

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
- Documento `.md` explicando el cambio solicitado, que hace, para que sirve, archivos tocados y pruebas realizadas.
- Verificacion ejecutada.
- Pull Request o merge hacia `develop` cuando el cambio este listo.

## Documentacion por cambio

Cada cambio solicitado debe documentarse en un archivo Markdown dentro de `docs/cambios/`.

El nombre del archivo debe ser descriptivo y usar fecha o tema:

```text
docs/cambios/2026-09-11-reparto-trabajo.md
docs/cambios/crud-generacion.md
docs/cambios/servicios-reglas-solares.md
```

Cada documento debe incluir:

- objetivo del cambio;
- razon o necesidad;
- que hace la solucion;
- archivos principales modificados;
- pasos para probar;
- resultado de pruebas;
- notas o pendientes.

Plantilla sugerida:

```markdown
# Nombre del cambio

## Objetivo

## Motivo

## Que se hizo

## Archivos tocados

## Como probar

## Resultado de pruebas

## Pendientes
```

## Normas antes de subir cambios

- No subir secretos, claves API, credenciales ni archivos `.env`.
- No subir codigo que no ejecute localmente.
- No mezclar cambios no relacionados en un mismo commit.
- No modificar `main` directamente durante el desarrollo.
- No integrar ramas personales directamente a `main`.
- Integrar avances solamente hacia `develop` hasta que el proyecto este terminado.
- No borrar historial remoto sin acuerdo del equipo.
- Revisar `git status` antes de cada commit y antes de cada push.
- Mantener mensajes de commit en español, claros y en modo descriptivo.
- Agregar o actualizar el `.md` correspondiente en `docs/cambios/` para cada cambio solicitado.
