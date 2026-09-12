# Ajuste visual del dashboard principal

## Objetivo

Hacer que el dashboard principal aproveche mejor el espacio disponible y recupere una composicion mas visual, clara y completa.

## Motivo

La vista anterior se sentia apagada y dejaba demasiado espacio sin usar. La referencia original usaba un hero mas fuerte, KPIs amplios y secciones informativas mejor distribuidas.

## Cambios realizados

- Se amplio el hero del dashboard con un titulo mas expresivo.
- Se agrego un bloque de contexto con departamento y acceso al mapa.
- Se reorganizaron los indicadores principales en cuatro KPIs amplios.
- Se amplio la grafica de generacion real vs esperada.
- Se agrego un panel de resumen nacional con equivalencias.
- Se reorganizo la parte inferior con alertas recientes y granjas registradas.
- La grafica usa colores del tema para mantener compatibilidad con modo claro y oscuro.
- Se compacto la altura de hero, KPIs, grafica y secciones inferiores para evitar desplazamiento vertical innecesario.
- Se retiro el filtro visual de departamento del dashboard porque no tenia funcionalidad propia en esa vista.
- Se ajusto el hero para que el titulo se lea completo.
- Se corrigio la alineacion de iconos en las tarjetas KPI.
- Se permitio que la parte inferior mantenga su contenido visible sin cortes bruscos.
- Se redujo la altura de la grafica y del resumen nacional para que no dominen la pantalla.
- Se estabilizo la alineacion de los iconos KPI usando una composicion flexible y centrado real del SVG.
- Se compacto nuevamente el dashboard: hero, KPIs, grafica, resumen y secciones inferiores ocupan menos espacio.
- Se ajusto el titulo principal para mantenerse en una sola linea en escritorio.
- Se corrigio una regla CSS que afectaba los `span` de los KPIs y descentraba los iconos dentro de sus cuadros.
- Se redujo aun mas la altura de la grafica y del resumen nacional para que ocupen menos espacio vertical.
- Se limito con altura real la franja de grafica y resumen nacional para evitar que crezcan por el ancho disponible.
- Se redujo ligeramente el hero y se aumento de forma moderada la franja de grafica/resumen para equilibrar la composicion.

## Archivos tocados

- `resources/views/dashboard.blade.php`
- `CHANGELOG.md`
- `docs/cambios/2026-09-12-dashboard-composicion-amplia.md`

## Verificacion

```bash
php artisan test
```

Resultado esperado: todas las pruebas pasan y el dashboard se visualiza con una distribucion mas completa.
