# Guia para colaboradores

## Objetivo

Crear una guia clara para que cualquier integrante del equipo pueda clonar, instalar, ejecutar, probar y trabajar en el proyecto siguiendo el flujo del repositorio.

## Motivo

El proyecto se trabaja en pareja y puede ser apoyado por herramientas de IA. La guia permite que otro integrante, o la IA de ese integrante, entienda rapidamente como correr el sistema y como subir cambios sin romper las normas del equipo.

## Que se hizo

- Se creo `docs/GUIA_COLABORADOR.md`.
- Se documentaron requisitos, instalacion, configuracion de `.env`, migraciones, seeders y servidor local.
- Se explico como crear ramas de trabajo desde `develop`.
- Se agregaron normas de commits, pruebas y documentacion por cambio.
- Se incluyo una recomendacion para trabajar con IA usando los documentos del proyecto como contexto.

## Archivos tocados

- `docs/GUIA_COLABORADOR.md`
- `docs/cambios/2026-09-11-guia-colaborador.md`
- `CHANGELOG.md`

## Como probar

1. Revisar que `docs/GUIA_COLABORADOR.md` exista.
2. Seguir los comandos de instalacion en una copia limpia del proyecto.
3. Ejecutar `php artisan test`.

## Resultado de pruebas

```text
php artisan test -> 7 passed
```

## Pendientes

- Actualizar la guia si se cambia la base de datos final a MySQL o PostgreSQL.
- Agregar instrucciones de deploy cuando se defina la plataforma en nube.
