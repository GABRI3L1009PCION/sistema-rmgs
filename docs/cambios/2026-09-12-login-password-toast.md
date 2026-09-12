# Mejora de login y notificaciones

## Objetivo

Mejorar la experiencia de acceso y la forma en que el sistema muestra mensajes de exito o error.

## Motivo

El formulario de login no permitia ver temporalmente la contraseña escrita. Ademas, los mensajes del sistema se mostraban como una tarjeta simple dentro del contenido, lo que se sentia poco visible y poco pulido.

## Cambios realizados

- Se agrego un boton con icono de ojo para mostrar u ocultar la contraseña en el login.
- Se agrego manejo de accesibilidad basica en el boton de contraseña con `aria-label`.
- Se reemplazo el mensaje simple de `session('status')` por notificaciones flotantes.
- Se agregaron toasts de exito y error.
- Las notificaciones se pueden cerrar manualmente.
- Las notificaciones desaparecen automaticamente despues de unos segundos.
- Los toasts respetan la paleta clara y oscura del sistema.

## Archivos tocados

- `resources/views/auth/login.blade.php`
- `resources/views/layouts/app.blade.php`
- `CHANGELOG.md`
- `docs/cambios/2026-09-12-login-password-toast.md`

## Verificacion sugerida

```bash
php artisan test
```

Tambien revisar manualmente:

- Mostrar y ocultar contraseña en `/login`.
- Crear, editar o desactivar un registro para ver toast de exito.
- Enviar un formulario con datos invalidos para ver toast de error.
