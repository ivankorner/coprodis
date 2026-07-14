-- Agregar columna para título de sección inicial en formularios
ALTER TABLE forms 
ADD COLUMN seccion_inicial_titulo VARCHAR(255) DEFAULT 'General' AFTER descripcion;