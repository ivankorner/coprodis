ALTER TABLE audits
ADD COLUMN tipo_audit ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info' AFTER modulo,
ADD COLUMN detalles TEXT NULL AFTER descripcion,
ADD COLUMN entidad VARCHAR(50) NULL AFTER user_agent,
ADD COLUMN entidad_id INT NULL AFTER entidad,
ADD INDEX idx_audits_tipo (tipo_audit),
ADD INDEX idx_audits_entidad (entidad, entidad_id);
