# Historial de cambios

Formato basado en cambios incrementales para evidenciar avance durante la competencia.

## 2026-09-11

### Cambiado

- Compatibilizacion de dependencias para ejecutar el proyecto con PHP 8.3 mediante `composer update -W`.
- Correccion del factor de CO2 evitado de `0.70` a `0.40 kg CO2/kWh`, alineado con las bases de la competencia.

### Agregado

- Pantalla para editar granjas solares.
- Accion para desactivar granjas solares sin borrar su historial.
- Formulario para crear registros historicos de generacion mensual.
- Pantalla para editar registros historicos de generacion.
- Accion para eliminar registros historicos de generacion.
- Tabla de historial de generacion en el dashboard.
- Manual breve de usuario dentro del README.

### Agregado

- Inicializacion del proyecto Laravel para el Sistema RMGS.
- Modelos, migraciones y relaciones principales: departamentos, granjas solares, paneles, registros de generacion y alertas.
- Seeders con los 22 departamentos de Guatemala y datos demo.
- Dashboard con indicadores nacionales, grafica comparativa, mapa interactivo, reportes, alertas y proyeccion.
- API REST con endpoints de departamentos, granjas, generacion, estadisticas y documentacion minima.
- README con objetivo, instalacion, endpoints, reglas de calculo y diagrama ER.
- PROMPTS.md como evidencia del uso de IA.

### Verificado

- `php artisan migrate:fresh --seed`
- `php artisan test`

## Convencion para proximos cambios

- `Agregado`: funcionalidades nuevas.
- `Cambiado`: mejoras sobre funcionalidades existentes.
- `Corregido`: errores solucionados.
- `Documentado`: cambios en README, prompts, API o evidencias.
- `Verificado`: comandos usados para validar el cambio.
