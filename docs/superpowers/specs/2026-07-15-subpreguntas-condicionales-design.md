# Sub-preguntas condicionales: Posicionamiento y Diferenciación Visual

## Problema

En `views/forms/edit.php:584`, `addConditionalField()` usa `this.fields.push(newField)`, agregando la sub-pregunta condicional al **final** del array de campos. Esto hace que en la lista de campos (columna izquierda del editor), la sub-pregunta aparezca lejos de su campo padre radio/select, dificultando la comprensión de la jerarquía.

No existe diferenciación visual entre campos normales y sub-preguntas condicionales.

## Solución

### 1. Reposicionamiento al crear sub-preguntas

En `addConditionalField()`, reemplazar `push()` por inserción inteligente:

- Encontrar el índice del campo padre en `this.fields`
- Escanear desde el padre hacia adelante para encontrar el último hijo condicional de ese padre
- Insertar el nuevo campo **después** del último hijo (o después del padre si no tiene hijos)

Esto mantiene el orden jerárquico: padre → hijos condicionales → siguiente campo.

### 2. Diferenciación visual en la lista de campos

En el template de la lista de campos, detectar si un campo tiene `condicion_campo_padre_id` truthy y aplicar:

| Propiedad | Valor |
|-----------|-------|
| Margen izquierdo | `ml-8` |
| Borde izquierdo | `border-l-4 border-green-400` |
| Fondo | `bg-green-50/50` (verde muy suave) |
| Tipo badge | Verde en vez de azul: `text-green-700 bg-green-100` |
| Contenido badge | "Sub-pregunta" en vez del tipo de campo |
| Ícono | `fa-code-branch` junto a la etiqueta |

### 3. Restricción de movimiento

Las sub-preguntas condicionales no pueden moverse arriba de su padre. Si el campo es condicional y el campo de arriba es su padre o un hermano condicional, la flecha "subir" se deshabilita.

## Archivos a modificar

- `views/forms/edit.php` — único archivo con toda la lógica del form builder

## No-Go's

- No se modifica la lógica de guardado (`saveFields`)
- No se modifica el renderizado de formularios (`records/create.php`, `records/edit.php`)
- No se modifica el esquema de base de datos
- No se agregan nuevas dependencias
