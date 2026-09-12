# Login y control de sesion

## Objetivo

Hacer que el sistema RMGS inicie primero en una pantalla de login y que las vistas internas solo sean accesibles para usuarios autenticados.

## Motivo

El sistema administra datos de granjas solares, paneles, generacion, alertas y reportes. Aunque el reto no exige un modulo complejo de usuarios, un acceso inicial controlado mejora la presentacion del proyecto y deja preparada la base para roles o permisos en una siguiente etapa.

## Cambios realizados

- Se agrego `AuthController` para mostrar login, validar credenciales y cerrar sesion.
- Se creo la vista `resources/views/auth/login.blade.php` con identidad visual RMGS.
- Se protegieron las rutas web principales con middleware `auth`.
- Se dejaron las rutas `/api/*` sin login para conservar la consulta tecnica de endpoints.
- Se agrego un usuario demo desde el seeder:
  - Correo: `admin@rmgs.test`
  - Contrasena: `password`
- El layout ahora muestra el nombre del usuario autenticado.
- Se actualizaron pruebas para validar redireccion a login, inicio de sesion y cierre de sesion.
- Se actualizo el README con credenciales y flujo inicial.

## Archivos tocados

- `app/Http/Controllers/AuthController.php`
- `routes/web.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/auth/login.blade.php`
- `resources/views/layouts/app.blade.php`
- `tests/Feature/AuthFlowTest.php`
- `tests/Feature/ExampleTest.php`
- `tests/Feature/ManagementPagesTest.php`
- `README.md`
- `CHANGELOG.md`

## Verificacion sugerida

```bash
php artisan migrate:fresh --seed
php artisan test
```

Luego abrir `http://127.0.0.1:8000`; el sistema debe redirigir al login antes de mostrar el dashboard.

## Pendiente

- Definir roles si el jurado o el equipo pide separar permisos entre administradores, operadores y lectores.
- Reemplazar credenciales demo por usuarios reales antes de una entrega productiva.
