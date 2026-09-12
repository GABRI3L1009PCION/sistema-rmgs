# Correccion de palabras con letra eñe

## Objetivo

Corregir textos visibles y documentacion donde faltaba la letra `ñ`.

## Motivo

Algunos textos estaban escritos sin `ñ` en palabras de uso visible dentro del sistema. Esto afectaba la presentacion del sistema y la documentacion del proyecto.

## Cambios realizados

- Se corrigieron textos del dashboard, login, alertas, reportes, proyecciones y exportacion CSV.
- Se actualizaron documentos del proyecto donde aparecian palabras sin `ñ`.
- Se evitaron cambios en cache de Laravel, dependencias y archivos de configuracion en ingles.

## Archivos tocados

- `app/Http/Controllers/DashboardController.php`
- `resources/views/auth/login.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/alerts/index.blade.php`
- `resources/views/reports/index.blade.php`
- `resources/views/records/edit.blade.php`
- `resources/views/projections/index.blade.php`
- `README.md`
- `PROMPTS.md`
- `CHANGELOG.md`
- `docs/ESTADO_Y_REPARTO.md`
- `docs/FLUJO_GIT.md`
- `docs/RUBRICA_CHECKLIST.md`
- `docs/cambios/2026-09-12-login-password-toast.md`
- `docs/cambios/2026-09-12-login-sesion.md`
- `docs/cambios/2026-09-12-paleta-modo-oscuro-header.md`

## Verificacion

```bash
php artisan test
```

Resultado esperado: todas las pruebas pasan.
