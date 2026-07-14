-- ============================================
-- MIGRACIÓN: LÓGICA CONDICIONAL EN FORMULARIOS
-- ============================================

-- Agregar columnas para lógica condicional
ALTER TABLE form_fields
    ADD COLUMN condicion_campo_padre INT NULL AFTER orden,
    ADD COLUMN condicion_valor VARCHAR(255) NULL AFTER condicion_campo_padre;

-- Foreign key al campo padre (puede ser NULL si no tiene condición)
ALTER TABLE form_fields
    ADD CONSTRAINT fk_condicion_campo_padre
    FOREIGN KEY (condicion_campo_padre) REFERENCES form_fields(id)
    ON DELETE SET NULL;
