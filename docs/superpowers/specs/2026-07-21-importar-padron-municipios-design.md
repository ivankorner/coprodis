# Importación de Padrón a Gestión de Municipios

## Objetivo
Importar 5,849 registros del archivo `PADRON.xlsx` al formulario "Gestión de Municipios" (ID 1) del sistema COPRODIS, insertando directamente en base de datos vía script CLI.

## Enfoque
Script PHP CLI (`importar_padron.php`) que:
1. Lee el Excel con PhpSpreadsheet
2. Mapea columnas a campos del formulario con normalización de valores
3. Omite duplicados por DNI
4. Inserta directo en `records` + `record_data`

## Mapeo Excel → Formulario

Se mapean ~50 columnas del Excel a los 139 campos del formulario. Columnas sin correspondencia se ignoran; campos sin datos quedan vacíos.

### Normalizaciones clave
- Sexo: MASCULINO/FEMENINO → Masculino/Femenino
- CUD: TIENE/NO/EN TRAMITE/VENCIDO → Sí/No/En trámite/Vencido
- Zona: URBANA/RURAL → Urbana/Rural
- Sí/No: SI/NO → Sí/No (con tilde)
- Tipo discapacidad: FISICA O MOTORA→Motora, MENTAL→Intelectual, etc.
- Case-insensitive matching para selects (Municipio, Parentesco, etc.)

## Estructura del script
1. Bootstrap (autoload, .env, Database)
2. Cargar field_id de form_fields para form_id=1
3. Por cada fila del Excel:
   a. Verificar DNI duplicado (query a record_data)
   b. Iniciar transacción
   c. INSERT en records
   d. Por cada campo mapeado, INSERT en record_data
   e. Commit
4. Reporte final

## Ejecución
`/Applications/XAMPP/xamppfiles/bin/php importar_padron.php`

## Output
- Progreso en terminal con contador
- Log de errores en `storage/logs/`
- Reporte: importados, omitidos, errores
