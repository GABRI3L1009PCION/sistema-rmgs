# Prompts usados durante el desarrollo

Este archivo sirve como evidencia del uso de IA durante la competencia.

## Analisis del reto

```text
Analiza las bases y la rubrica de una competencia de programacion con IA. Identifica los criterios de evaluacion, los pesos y una estrategia para maximizar puntaje.
```

## Alcance MVP

```text
El reto pide una aplicacion Laravel para registro y monitoreo de generacion solar en Guatemala por departamento. Propone un MVP que cumpla dashboard, mapa, alertas, reportes, proyeccion y API REST.
```

## Modelo de datos

```text
Diseña un modelo relacional para departamentos, granjas solares, modelos de paneles, paneles por granja, registros de generacion y alertas. Incluye reglas para capacidad instalada, CO2 evitado y desviacion de desempeño.
```

## Implementacion

```text
Crea una aplicacion Laravel con migraciones, modelos, seeders, controladores, rutas web/API y vistas Blade para un sistema de monitoreo de granjas solares en Guatemala.
```

## Verificacion

```text
Ejecuta migraciones, seeders, rutas y pruebas. Corrige errores hasta que la aplicacion cargue correctamente y los tests pasen.
```

## Documentacion

```text
Genera un README breve con objetivo, funcionalidades, tecnologias, instalacion, endpoints API, reglas de calculo y diagrama de base de datos en Mermaid.
```

## Correccion de requerimientos

```text
Revisa las bases de la competencia contra el codigo Laravel existente e identifica inconsistencias de reglas de negocio. Corrige el factor de CO2 a 0.40 kg CO2/kWh y actualiza codigo, seeders, API y documentacion.
```

## CRUD faltante

```text
Completa los requerimientos pendientes del sistema: editar/desactivar granjas solares y administrar registros historicos de generacion. Mantener validaciones, recalculo de CO2 y generacion automatica de alertas cuando la generacion real sea 20% menor que la esperada.
```
