# Estado del proyecto y reparto de trabajo

Este documento resume que partes del reto ya estan implementadas, que falta completar y como se divide el trabajo entre los dos integrantes del equipo.

## Estado actual

### Ya implementado

- Proyecto base en Laravel.
- Repositorio GitHub configurado.
- Ramas principales: `main`, `develop` y ramas personales/de trabajo.
- Flujo Git documentado.
- `CHANGELOG.md` para registrar cambios.
- `PROMPTS.md` como evidencia del uso de IA.
- `README.md` tecnico con instalacion, endpoints y reglas de calculo.
- Diagrama de base de datos en Mermaid.
- Modelos principales:
  - departamentos;
  - granjas solares;
  - modelos de paneles;
  - paneles por granja;
  - registros de generacion;
  - alertas.
- Migraciones iniciales.
- Seeders con los 22 departamentos de Guatemala.
- Datos demo para granjas, paneles, generacion y alertas.
- Dashboard principal con indicadores nacionales.
- Grafica de generacion real vs esperada.
- Mapa interactivo con ubicacion de granjas solares.
- Reporte/ranking por departamento.
- Calculo de capacidad instalada.
- Calculo de CO2 evitado.
- Deteccion de alertas cuando la generacion real esta 20% o mas debajo de la esperada.
- Proyeccion simple por promedio movil.
- Servicio centralizado para reglas solares.
- Pruebas unitarias de reglas solares.
- API REST basica:
  - departamentos;
  - granjas;
  - generacion;
  - estadisticas;
  - documentacion minima.
- Formulario rapido para crear una granja con registro inicial.
- Pruebas base y pruebas de reglas solares ejecutandose correctamente.

### Implementado de forma parcial

- CRUD de granjas solares: existe creacion, falta editar, consultar detalle y desactivar.
- Registro de generacion: existe por datos demo y registro inicial, falta CRUD historico por periodo.
- Paneles solares: existen modelos y relacion con granjas, falta administracion desde interfaz.
- Validaciones: existen validaciones basicas, falta organizarlas y ampliar casos.
- Documentacion: existe base tecnica, falta manual de usuario con capturas.
- Uso de IA: existe `PROMPTS.md`, faltan capturas o evidencia visual.
- API REST: existe base funcional, falta ampliar documentacion y ejemplos de respuesta.
- UI/UX: existe dashboard funcional, falta pulido visual y revision responsive final.

### Pendiente importante

- Deploy publico en la nube.
- Configuracion final para MySQL o PostgreSQL.
- CRUD completo de granjas solares.
- CRUD de modelos de paneles.
- Administracion de paneles por granja.
- CRUD de registros historicos de generacion.
- Vista de detalle por granja.
- Desactivacion de granjas.
- Manual de usuario.
- Capturas de evidencia de uso de IA.
- Presentacion final.
- Ensayo de demo en vivo.

## Prioridad de desarrollo

1. Completar CRUD historico de generacion.
2. Completar CRUD de granjas solares.
3. Agregar vista de detalle por granja.
4. Mejorar reglas de negocio y pruebas adicionales de integracion.
5. Preparar deploy.
6. Completar documentacion de usuario y evidencias.
7. Preparar presentacion final.

## Reparto de trabajo

### Parte 1: Integrante encargado de base, arquitectura y control de calidad

Responsabilidades sugeridas:

- Mantener el flujo Git del equipo.
- Revisar que los cambios entren primero a `develop`.
- Mejorar la estructura interna del proyecto.
- Extraer reglas de negocio a servicios cuando aplique.
- Crear pruebas para calculos criticos.
- Documentar cambios en `CHANGELOG.md`.
- Mantener `README.md`, `PROMPTS.md` y documentos de apoyo.
- Preparar o apoyar el deploy.
- Revisar que no se suban secretos ni archivos `.env`.

Tareas concretas:

- Crear servicios para calculos solares:
  - capacidad instalada;
  - CO2 evitado;
  - porcentaje de desviacion;
  - regla de alerta del 20%;
  - proyeccion por promedio movil.
- Agregar tests unitarios para las reglas anteriores.
- Mejorar la documentacion de la API con ejemplos.
- Coordinar la integracion de ramas hacia `develop`.
- Preparar checklist final antes de pasar a `main`.

Commits esperados:

```text
Agregar servicios para reglas solares [hecho]
Agregar pruebas de calculos solares [hecho]
Documentar reglas de negocio
Preparar configuracion para despliegue
```

### Parte 2: Integrante encargado de funcionalidades CRUD e interfaz

Responsabilidades sugeridas:

- Completar pantallas de administracion.
- Mejorar la experiencia de usuario.
- Implementar flujos de registro, edicion y consulta.
- Probar manualmente los formularios.
- Apoyar con capturas para manual de usuario y presentacion.

Tareas concretas:

- Completar CRUD de granjas solares:
  - listado;
  - detalle;
  - edicion;
  - desactivacion.
- Crear CRUD de generacion por periodo:
  - registrar mes o periodo;
  - generacion esperada;
  - generacion real;
  - notas;
  - recalculo de alerta.
- Crear CRUD o pantalla de administracion de modelos de paneles.
- Crear administracion de paneles instalados por granja.
- Mejorar vistas responsive y mensajes de validacion.
- Preparar capturas del sistema para manual y presentacion.

Commits esperados:

```text
Completar listado de granjas
Agregar edicion de granjas solares
Agregar registro historico de generacion
Agregar administracion de paneles por granja
Mejorar mensajes de validacion
```

## Normas para ambos integrantes

- Trabajar siempre en una rama propia o de funcionalidad.
- Integrar cambios solo hacia `develop` durante el desarrollo.
- No subir cambios directos a `main`.
- Ejecutar `php artisan test` antes de subir.
- Actualizar `CHANGELOG.md` con cada cambio importante.
- Registrar prompts relevantes en `PROMPTS.md` cuando se use IA.
- Usar commits pequenos y claros.
- Evitar mezclar funcionalidades distintas en un mismo commit.
- No subir credenciales, claves, tokens ni `.env`.

## Criterio para pasar a main

El paso de `develop` a `main` solo debe hacerse cuando:

- la aplicacion este terminada para la entrega;
- las pruebas pasen;
- el deploy publico funcione;
- la documentacion este completa;
- la presentacion y demo esten listas;
- el equipo este de acuerdo con la version final.
