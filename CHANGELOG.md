# Historial de cambios

Formato basado en cambios incrementales para evidenciar avance durante la competencia.

## 2026-09-11

### Cambiado

- Integrados en `develop` los cambios que fueron enviados a `main` por error, conservando tambien el trabajo previo de `develop`.
- Compatibilizacion de dependencias para ejecutar el proyecto con PHP 8.3 mediante `composer update -W`.
- Correccion del factor de CO2 evitado de `0.70` a `0.40 kg CO2/kWh`, alineado con las bases de la competencia.
- Actualizada la documentacion general para reflejar el servicio de reglas solares y sus pruebas unitarias.
- Actualizado el flujo Git para describir normas generales de ramas, commits, push e integracion sin mencionar usuarios especificos.
- Actualizado el checklist de rubrica con control de versiones y evidencia de trabajo incremental.
- Reforzada la regla de integracion: las ramas personales solo se integran a `develop`; `main` se reserva para la version final terminada y probada.
- Agregada la norma de crear un documento `.md` por cada cambio solicitado, explicando objetivo, alcance, archivos tocados y pruebas.

### Agregado

- Pantalla para editar granjas solares.
- Accion para desactivar granjas solares sin borrar su historial.
- Formulario para crear registros historicos de generacion mensual.
- Pantalla para editar registros historicos de generacion.
- Accion para eliminar registros historicos de generacion.
- Tabla de historial de generacion en el dashboard.
- Manual breve de usuario dentro del README.
- Guia para colaboradores en `docs/GUIA_COLABORADOR.md` con pasos para clonar, instalar, correr, probar y trabajar con ramas.
- Documento `docs/cambios/2026-09-11-guia-colaborador.md` con el detalle del cambio.
- Pruebas unitarias para las reglas solares criticas.
- Documento `docs/cambios/2026-09-11-pruebas-reglas-solares.md` con el detalle de las pruebas agregadas.
- Servicio `SolarMetricsService` para centralizar calculos de capacidad instalada, CO2 evitado, desviacion, alertas y proyeccion.
- Documento `docs/cambios/2026-09-11-servicio-reglas-solares.md` con el detalle del cambio.
- Documento `docs/ESTADO_Y_REPARTO.md` con avance actual, pendientes y division de trabajo entre los integrantes.
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
