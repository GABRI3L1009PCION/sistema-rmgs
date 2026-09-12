# Guia para colaboradores

Esta guia explica como levantar el proyecto, crear una rama de trabajo y subir cambios siguiendo las normas del repositorio.

## Requisitos

- PHP 8.3 o superior.
- Composer.
- Git.
- SQLite para entorno local rapido.

Opcional para etapas finales:

- MySQL o PostgreSQL.
- Node.js y npm si se agregan assets compilados.

## Clonar el proyecto

```bash
git clone https://github.com/GABRI3L1009PCION/sistema-rmgs.git
cd sistema-rmgs
```

## Usar la rama de desarrollo

El desarrollo se hace desde `develop`, no desde `main`.

```bash
git switch develop
git pull origin develop
```

## Configurar usuario Git local

Cada integrante debe configurar su usuario solo dentro de este repositorio:

```bash
git config user.name "Nombre del integrante"
git config user.email "correo_institucional@miumg.edu.gt"
```

Verificar:

```bash
git config --local --list
```

## Instalar dependencias

```bash
composer install
```

## Crear archivo de entorno

En Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

En Git Bash, Linux o macOS:

```bash
cp .env.example .env
```

Generar clave de Laravel:

```bash
php artisan key:generate
```

## Preparar base de datos local

El proyecto usa SQLite por defecto para facilitar la demo local.

```bash
php artisan migrate:fresh --seed
```

Este comando:

- borra tablas existentes;
- crea la estructura;
- carga los 22 departamentos;
- carga datos demo de granjas, paneles, generacion y alertas.

## Levantar servidor local

```bash
php artisan serve
```

Abrir:

```text
http://127.0.0.1:8000
```

## Verificar que funciona

Ejecutar:

```bash
php artisan test
```

Resultado esperado actualmente:

```text
7 passed
```

## Endpoints utiles

- Dashboard: `http://127.0.0.1:8000`
- Documentacion API: `http://127.0.0.1:8000/api/docs`
- Estadisticas API: `http://127.0.0.1:8000/api/stats`
- Departamentos: `http://127.0.0.1:8000/api/departments`
- Granjas: `http://127.0.0.1:8000/api/farms`
- Generacion: `http://127.0.0.1:8000/api/generation`

## Crear una rama de trabajo

Cada integrante debe trabajar en su propia rama o en una rama de funcionalidad.

```bash
git switch develop
git pull origin develop
git switch -c feature/nombre-del-cambio
```

Ejemplos:

```text
feature/crud-granjas
feature/generacion-historica
feature/paneles-por-granja
docs/manual-usuario
```

## Antes de hacer commit

Revisar cambios:

```bash
git status
```

Ejecutar pruebas:

```bash
php artisan test
```

Actualizar documentacion obligatoria:

- `CHANGELOG.md`: registrar el cambio.
- `PROMPTS.md`: registrar prompts importantes si se uso IA.
- `docs/cambios/*.md`: crear un documento por cada cambio solicitado.

## Documento obligatorio por cambio

Cada cambio debe tener un archivo en:

```text
docs/cambios/
```

Debe explicar:

- objetivo;
- motivo;
- que se hizo;
- archivos tocados;
- como probar;
- resultado de pruebas;
- pendientes.

## Hacer commit

```bash
git add .
git commit -m "Descripcion clara del cambio"
```

Usar mensajes naturales y claros, por ejemplo:

```text
Agregar registro historico de generacion
Actualizar formulario de granjas solares
Corregir validacion de capacidad instalada
Documentar endpoints de generacion
```

## Subir rama

```bash
git push -u origin feature/nombre-del-cambio
```

## Integrar cambios

Durante el desarrollo, los cambios se integran hacia `develop`.

Flujo correcto:

```text
rama personal o feature -> develop
```

Flujo incorrecto durante desarrollo:

```text
rama personal o feature -> main
```

`main` se actualizara solo cuando el proyecto este terminado, probado y listo para entrega.

## Archivos que no deben subirse

- `.env`
- credenciales;
- claves API;
- tokens;
- archivos temporales;
- datos privados.

## Recomendacion para trabajar con IA

Antes de pedir codigo a una IA, compartirle estos archivos:

- `README.md`
- `docs/FLUJO_GIT.md`
- `docs/ESTADO_Y_REPARTO.md`
- `docs/GUIA_COLABORADOR.md`
- `docs/RUBRICA_CHECKLIST.md`

Despues de usar IA:

- revisar el codigo generado;
- probarlo localmente;
- documentar prompts importantes en `PROMPTS.md`;
- crear el `.md` del cambio en `docs/cambios/`;
- no subir nada que no se pueda explicar.
