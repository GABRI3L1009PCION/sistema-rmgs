# Historial de cambios

Formato basado en cambios incrementales para evidenciar avance durante la competencia.

## 2026-09-11

### Cambiado

- Dashboard reorganizado para aprovechar mejor el espacio: hero mas expresivo, filtros/contexto, KPIs amplios, resumen nacional y secciones inferiores.
- Dashboard compactado despues del rediseño para evitar scroll vertical por crecimiento excesivo de sus secciones.
- Dashboard corregido para retirar filtro no funcional, mejorar lectura del titulo, centrar iconos de KPIs y evitar cortes en la parte inferior.
- Dashboard refinado para reducir el espacio de grafica/resumen y estabilizar el centrado de iconos KPI.
- Dashboard compactado nuevamente para reducir tamaños generales y mantener el titulo principal en una sola linea en escritorio.
- Dashboard corregido para que las reglas de texto no desplacen los iconos dentro de las tarjetas KPI.
- Textos visibles y documentacion corregidos para usar la letra `ñ` donde corresponde.
- Dashboard ajustado para que la grafica y el resumen nacional ocupen menos espacio vertical.
- Header reforzado para permanecer por encima del contenido en todas las vistas.
- Dashboard rebalanceado: hero mas compacto y grafica/resumen con un poco mas de altura.
- Selector de periodo del dashboard convertido en filtro funcional sobre los datos mensuales.
- Filtro de periodo del dashboard actualizado para refrescar grafica y resumen sin recargar la pagina.
- Dashboard completado para que tendencia CO2, alertas recientes y top de granjas respondan al periodo seleccionado.
- Vista de granjas actualizada con acceso visible para registrar una nueva granja solar.
- Formulario de nueva granja actualizado para sugerir generacion esperada mensual a partir de capacidad instalada.
- Granjas solares actualizado para sincronizar filtros de departamento/municipio con tabla y mapa, y validar municipios por departamento.
- Notificaciones del sistema rediseñadas como toasts flotantes con cierre automatico.
- Modo oscuro corregido en reportes, alertas, proyecciones y mapa para evitar tarjetas blancas, tablas claras y graficas con contraste incorrecto.
- Paleta visual global rediseñada con superficies mas calidas, mejor contraste y soporte de modo oscuro.
- Menu de usuario del header rediseñado como dropdown compacto con cierre de sesion.
- Cambio de tema movido al header con animacion visual entre modo claro y modo oscuro.
- Flujo principal protegido con autenticacion: al entrar al sistema primero se muestra el login.
- Apartado de configuracion/parametros retirado de la interfaz para mantener el sistema enfocado en las funciones centrales del reto.
- Paleta global suavizada para una apariencia mas limpia y menos cargada de azul palido.
- Vista dashboard ajustada para funcionar como resumen informativo sin acciones de creacion, con hero a ancho completo y KPIs separados.
- Barra global de busqueda, periodo y acciones retirada del layout principal porque cada modulo cuenta con filtros propios.
- Menu lateral actualizado para poder ocultarse y mostrarse, conservando la preferencia en el navegador.
- Dashboard compactado para verse completo en escritorio sin desplazamiento vertical, con resumen ejecutivo de granjas y alertas.
- Limpieza visual de pantallas principales para diferenciar mejor dashboard, generacion, reportes, mapa, alertas, paneles y configuracion.
- Barra superior simplificada para mostrar usuario y control de menu, retirando controles duplicados.
- Dashboard reorganizado como vista de monitoreo nacional con KPIs reales, grafica, ranking y alertas.
- Pantalla de generacion reorganizada como vista historica con filtros, graficas, tabla completa y acciones.
- Paneles reorganizado como catalogo tecnico con filtros, lista y detalle lateral, diferenciandolo del dashboard.
- Textos de KPIs ajustados para evitar tendencias fijas no calculadas.
- Reportes ajustado para mostrar "Vista imprimible" y "Exportar CSV" segun la funcionalidad real.
- Configuracion ajustada para que sus accesos funcionen como enlaces a secciones del formulario.
- Integrados en `develop` los cambios que fueron enviados a `main` por error, conservando tambien el trabajo previo de `develop`.
- Compatibilizacion de dependencias para ejecutar el proyecto con PHP 8.3 mediante `composer update -W`.
- Correccion del factor de CO2 evitado de `0.70` a `0.40 kg CO2/kWh`, alineado con las bases de la competencia.
- Actualizada la documentacion general para reflejar el servicio de reglas solares y sus pruebas unitarias.
- Actualizado el flujo Git para describir normas generales de ramas, commits, push e integracion sin mencionar usuarios especificos.
- Actualizado el checklist de rubrica con control de versiones y evidencia de trabajo incremental.
- Reforzada la regla de integracion: las ramas personales solo se integran a `develop`; `main` se reserva para la version final terminada y probada.
- Agregada la norma de crear un documento `.md` por cada cambio solicitado, explicando objetivo, alcance, archivos tocados y pruebas.

### Agregado

- Boton para mostrar u ocultar la contraseña en el login.
- Documento `docs/cambios/2026-09-12-acceso-nueva-granja.md` con el detalle del acceso visible al registro de granjas.
- Documento `docs/cambios/2026-09-12-generacion-esperada-automatica.md` con el detalle del calculo sugerido de generacion esperada.
- Documento `docs/cambios/2026-09-12-granjas-filtros-municipios.md` con el detalle de filtros y municipios dependientes.
- Alternador de tema claro/oscuro persistente en el navegador.
- Pantalla de login, rutas de sesion y usuario demo sembrado para acceder al sistema.
- Pruebas de autenticacion para validar carga del login, acceso con usuario demo y cierre de sesion.
- Vista de detalle por granja solar con datos generales, KPIs, paneles instalados, historial de generacion y alertas.
- Administracion de paneles instalados por granja: agregar, actualizar cantidad y retirar paneles.
- Pantalla para editar modelos de paneles solares.
- Accion para desactivar modelos de paneles solares.
- Accesos desde listados hacia detalle, edicion y desactivacion de granjas y paneles.
- Pruebas de humo para validar pantallas de detalle de granja y edicion de panel.
- Documento `docs/cambios/2026-09-11-cierre-crud-paneles-granjas.md` con el detalle del cierre funcional.
- Documento `docs/cambios/2026-09-11-limpieza-visual-vistas.md` con el detalle del rediseño visual.
- Documento `docs/cambios/2026-09-11-menu-plegable-dashboard-compacto.md` con el detalle del menu plegable y dashboard compacto.
- Documento `docs/cambios/2026-09-12-retiro-parametros-sistema.md` con el detalle del retiro del apartado de parametros.
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
- `php artisan route:list --name=farms`
- `php artisan route:list --name=panels`
- Revision HTTP local de rutas principales con respuestas `200`.

## Convencion para proximos cambios

- `Agregado`: funcionalidades nuevas.
- `Cambiado`: mejoras sobre funcionalidades existentes.
- `Corregido`: errores solucionados.
- `Documentado`: cambios en README, prompts, API o evidencias.
- `Verificado`: comandos usados para validar el cambio.
