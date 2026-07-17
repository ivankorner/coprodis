-- Seed data for coprodis_test
-- Roles and initial admin user are already created by 001_schema.sql migration

-- Test users (password: Test123!)
-- id=1 is occupied by the migration's default admin (admin@coprodis.com)
INSERT INTO users (id, apellido, nombre, dni, email, password, rol_id, estado) VALUES
    (2, 'Admin', 'Sistema', '11111111', 'admin@test.com', '$2y$10$fKKyG.wsUbttABeywJleU.zRWs4.9EWrByy/ZK2EZ7Z.JpdKygxve', 1, 'activo'),
    (3, 'Operador', 'Usuario', '22222222', 'operador@test.com', '$2y$10$fKKyG.wsUbttABeywJleU.zRWs4.9EWrByy/ZK2EZ7Z.JpdKygxve', 3, 'activo'),
    (4, 'Bloqueado', 'Usuario', '33333333', 'bloqueado@test.com', '$2y$10$fKKyG.wsUbttABeywJleU.zRWs4.9EWrByy/ZK2EZ7Z.JpdKygxve', 3, 'bloqueado');

-- Test forms (created_by = 2 = admin@test.com)
INSERT INTO forms (id, titulo, descripcion, estado, created_by) VALUES
    (1, 'Solicitud de Certificado', 'Formulario para solicitar certificado de discapacidad', 'publicado', 2),
    (2, 'Informe Social', 'Formulario para informe social', 'borrador', 2);

-- Form fields for Form 1 (Solicitud de Certificado)
INSERT INTO form_fields (id, form_id, tipo, nombre, etiqueta, placeholder, requerido, opciones, orden) VALUES
    (1, 1, 'texto', 'nombre_completo', 'Nombre completo', 'Ingrese nombre y apellido', TRUE, NULL, 0),
    (2, 1, 'numero', 'dni', 'DNI', 'Ingrese DNI', TRUE, NULL, 1),
    (3, 1, 'email', 'email', 'Correo electrónico', 'correo@ejemplo.com', TRUE, NULL, 2),
    (4, 1, 'telefono', 'telefono', 'Teléfono de contacto', '3764123456', TRUE, NULL, 3),
    (5, 1, 'fecha', 'fecha_nacimiento', 'Fecha de nacimiento', NULL, TRUE, NULL, 4),
    (6, 1, 'select', 'tipo_certificado', 'Tipo de certificado', NULL, TRUE, '["Certificado Único","Certificado Temporal","Renovación"]', 5),
    (7, 1, 'textarea', 'observaciones', 'Observaciones', 'Detalle adicional', FALSE, NULL, 6),
    (8, 1, 'select', 'medio_contacto', 'Medio de contacto preferido', NULL, FALSE, '["Email","Teléfono","WhatsApp","Correo postal"]', 7),
    (9, 1, 'checkbox', 'documentacion_adjunta', 'Documentación adjunta', NULL, FALSE, '["DNI","Certificado médico","Informe social","Otros"]', 8);

-- Form fields for Form 2 (Informe Social)
INSERT INTO form_fields (id, form_id, tipo, nombre, etiqueta, placeholder, requerido, orden) VALUES
    (10, 2, 'texto', 'asistente_social', 'Asistente Social', 'Nombre del asistente', TRUE, 0),
    (11, 2, 'textarea', 'informe', 'Informe', 'Detalle del informe social', TRUE, 1);

-- Conditional field for Form 1 (depends on field 8: medio_contacto = 'Email')
INSERT INTO form_fields (id, form_id, tipo, nombre, etiqueta, placeholder, requerido, condicion_campo_padre, condicion_valor, orden) VALUES
    (12, 1, 'texto', 'direccion_email', 'Dirección de email', 'ejemplo@correo.com', FALSE, 8, 'Email', 10);

-- Test records (user_id = 3 = operador@test.com)
INSERT INTO records (id, form_id, user_id, estado) VALUES
    (1, 1, 3, 'activo'),
    (2, 1, 3, 'activo'),
    (3, 1, 3, 'archivado');

-- Record data
INSERT INTO record_data (record_id, field_id, valor) VALUES
    (1, 1, 'Juan Pérez'), (1, 2, '12345678'), (1, 3, 'juan@ejemplo.com'),
    (1, 4, '3764000001'), (1, 5, '1990-05-15'), (1, 6, 'Certificado Único'),
    (1, 7, 'Solicitante presenta documentación completa'),
    (2, 1, 'María García'), (2, 2, '87654321'), (2, 3, 'maria@ejemplo.com'),
    (2, 4, '3764000002'), (2, 5, '1985-11-20'), (2, 6, 'Renovación'),
    (3, 1, 'Carlos López'), (3, 2, '11223344'), (3, 3, 'carlos@ejemplo.com'),
    (3, 4, '3764000003'), (3, 5, '1978-03-08'), (3, 6, 'Certificado Temporal');
