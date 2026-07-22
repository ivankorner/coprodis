# Reportes Redesign — Dashboard Unificado

## Objetivo
Hacer la sección `/reportes` más profesional: permitir seleccionar formulario para filtrar todos los gráficos, agregar más filtros (rango de fechas, chips rápidos), exportar gráficos como PNG, y mejorar el diseño visual con tarjetas modernas.

## Arquitectura

### Cambios en ReportController (`index`)
- Aceptar nuevos query params: `form_id`, `fecha_desde`, `fecha_hasta`
- Si `form_id` está presente y no es "todos", filtrar todas las queries por ese form
- Pasar `$forms` (lista completa de formularios) a la vista para el selector
- Pasar `$selectedFormId` para mantener selección al recargar

### Cambios en vista (`views/reports/index.php`)
- **Barra de filtros global** en la parte superior:
  - Selector de formulario (dropdown con "Todos los formularios")
  - Rango de fechas (desde / hasta + botón aplicar/limpiar)
  - Chips rápidos (Hoy, 7 días, 30 días, 90 días)
- **KPIs dinámicos** que se actualizan según filtros
- **Gráficos dinámicos**: la dona y el timeline reaccionan al form_id
- **Botón de exportar PNG** en cada canvas de Chart.js
- **Diseño**: tarjetas con sombra suave, bordes redondeados, espaciado mejorado

### Chart Export (PNG)
Usar `canvas.toDataURL()` + enlace de descarga forzada via `<a download>`.

## Componentes

### 1. Filtros globales
```html
<form method="GET" class="...">
  <select name="form_id"> ... </select>
  <input type="date" name="fecha_desde">
  <input type="date" name="fecha_hasta">
  <button type="submit">Aplicar</button>
  <a href="/reportes">Limpiar</a>
</form>
<div class="chips">
  <a href="?days=1">Hoy</a>
  <a href="?days=7">7 días</a>
  ...
</div>
```

### 2. KPIs
Se muestran siempre. Si hay form_id seleccionado, los totales reflejan solo ese formulario.

### 3. Gráficos
- **Dona**: Registros por formulario (si no hay filtro) o distribución de campos del formulario seleccionado
- **Línea**: Actividad temporal filtrada por form y rango de fechas

### 4. Export PNG
JavaScript que toma el canvas y fuerza descarga:
```js
function downloadChart(canvasId, filename) {
  const link = document.createElement('a');
  link.download = filename + '.png';
  link.href = document.getElementById(canvasId).toDataURL();
  link.click();
}
```

## Flujo de datos
1. Usuario selecciona formulario + fechas + clicks "Aplicar"
2. Formulario hace GET con query params
3. Controller recibe params, ajusta queries SQL (WHERE form_id = X, fechas)
4. Vista recibe datos filtrados + mantiene valores seleccionados
5. Charts se renderizan con datos filtrados

## Archivos modificados
- `controllers/ReportController.php` — método `index` con filtros
- `views/reports/index.php` — rediseño completo
- `views/reports/timeline.php` — mismo estilo moderno (opcional)
- `views/reports/form.php` — botón de export PNG (opcional)
