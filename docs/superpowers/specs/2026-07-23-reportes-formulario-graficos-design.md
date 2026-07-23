# Reportes Formulario — Vista de Gráficos Agrupados

## Objetivo
Rediseñar la vista `reportes/formulario/{id}` reemplazando el acordeón por campo por una grilla de tarjetas con gráficos agrupados por tipo de campo, más un listado final de campos de texto/separadores.

## Arquitectura

### Cambios en ReportController (`form`)
- Sin cambios en el controlador. Toda la lógica de analytics existente se reutiliza.
- Solo se modifica la vista.

### Cambios en vista (`views/reports/form.php`)
Eliminar el acordeón actual y reemplazar con dos secciones:

1. **Header + KPIs + Filtros** — se mantienen sin cambios
2. **Grilla de gráficos** — cards responsivas (2-3 columnas) con Chart.js
3. **Listado de campos de texto** — al final de la página

## Layout

```
┌──────────────────────────────────────────┐
│ Header (título, botones guardar/exportar)│
│ KPIs (registros, campos, estado, fecha)  │
│ Filtro de fechas (desde/hasta)           │
├──────────────────────────────────────────┤
│ ┌────────────┐ ┌────────────┐ ┌────────┐│
│ │ Edad       │ │ Género     │ │ Fecha  ││
│ │ [bar chart]│ │ [dona]     │ │[barras]││
│ │ avg: 34    │ │ M: 60%     │ │        ││
│ └────────────┘ └────────────┘ └────────┘│
│ ┌────────────┐ ┌────────────┐ ┌────────┐│
│ │ Ingresos   │ │ Ubicación  │ │ Hora   ││
│ │ [bar chart]│ │ [mapa]     │ │[barras]││
│ │ avg: 4500  │ │            │ │        ││
│ └────────────┘ └────────────┘ └────────┘│
├──────────────────────────────────────────┤
│ ─── Datos registrados ───               │
│ Nombre: 45 respuestas                    │
│ Email: 42 respuestas                     │
│ Teléfono: 38 respuestas                  │
│ ─ Separador: Datos personales ─          │
└──────────────────────────────────────────┘
```

## Componentes

### 1. Grilla de gráficos
Grilla CSS grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` con gap.

Cada card contiene:
- **Header**: `etiqueta` del campo
- **Canvas**: Chart.js según tipo
- **Stats**: badges opcionales (solo numéricos)

Tipos de gráfico por field type:
| Tipo | Chart.js tipo | Extras |
|------|---------------|--------|
| numero/moneda/porcentaje | bar (vertical) | avg, sum, min, max |
| select/radio | doughnut | — |
| checkbox | bar (horizontal) | — |
| fecha | bar (mensual) | — |
| hora | bar (por hora) | — |
| gps | Leaflet map | — |
| imagen/archivo/firma | icon + count | sin chart |

### 2. Listado de campos de texto
Sección al final con borde superior. Muestra una línea por cada campo tipo:
- texto, textarea, email, telefono, separador

Formato:
- **texto/email/tel/etc**: `"{etiqueta}": {filledCount} respuestas`
- **separador**: `"── Separador: {etiqueta} ──"`

### 3. Filtros de fechas
Se mantienen exactamente igual (controlador ya los maneja).

## Flujo de datos
1. Controller calcula `$fieldAnalytics` (sin cambios)
2. Vista itera `$fields` y `$fieldAnalytics`
3. En la grilla: solo fields graficables (numero, moneda, porcentaje, select, radio, checkbox, fecha, hora, gps, imagen, archivo, firma)
4. En el listado: fields de texto y separadores
5. Charts se instancian con Alpine.js `x-init` o script inline al final

## Archivos modificados
- `views/reports/form.php` — rediseño completo de la vista

## Archivos no modificados
- `controllers/ReportController.php` — sin cambios
- `views/layouts/main.php` — sin cambios
- `views/reports/index.php` — sin cambios
