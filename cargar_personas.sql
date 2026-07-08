-- ============================================
-- Script: Cargar 15 personas en "Gestión de Municipios"
-- Base: coprodis | Form: Prueba 1 (form_id=1)
-- ============================================

START TRANSACTION;

-- 1. Quitar required del campo Email
UPDATE form_fields SET requerido = 0 WHERE id = 17;

-- 2. Insertar registros (15 personas, form_id=1, user_id=1)
INSERT INTO records (id, form_id, user_id, estado, created_at, updated_at) VALUES
(5, 1, 1, 'activo', NOW(), NOW()),
(6, 1, 1, 'activo', NOW(), NOW()),
(7, 1, 1, 'activo', NOW(), NOW()),
(8, 1, 1, 'activo', NOW(), NOW()),
(9, 1, 1, 'activo', NOW(), NOW()),
(10, 1, 1, 'activo', NOW(), NOW()),
(11, 1, 1, 'activo', NOW(), NOW()),
(12, 1, 1, 'activo', NOW(), NOW()),
(13, 1, 1, 'activo', NOW(), NOW()),
(14, 1, 1, 'activo', NOW(), NOW()),
(15, 1, 1, 'activo', NOW(), NOW()),
(16, 1, 1, 'activo', NOW(), NOW()),
(17, 1, 1, 'activo', NOW(), NOW()),
(18, 1, 1, 'activo', NOW(), NOW()),
(19, 1, 1, 'activo', NOW(), NOW());

-- 3. Insertar datos de cada persona (field_id: 14=Apellido, 15=Nombre, 16=DNI)
INSERT INTO record_data (record_id, field_id, valor, created_at, updated_at) VALUES
-- NORMA BEATRIZ ALMEIDA - 22870744
(5, 14, 'ALMEIDA', NOW(), NOW()),
(5, 15, 'NORMA BEATRIZ', NOW(), NOW()),
(5, 16, '22870744', NOW(), NOW()),
-- LUCAS ALBERTO SILVERO - 52310369
(6, 14, 'SILVERO', NOW(), NOW()),
(6, 15, 'LUCAS ALBERTO', NOW(), NOW()),
(6, 16, '52310369', NOW(), NOW()),
-- CARLOS OSVALDO BRITEZ - 26342665
(7, 14, 'BRITEZ', NOW(), NOW()),
(7, 15, 'CARLOS OSVALDO', NOW(), NOW()),
(7, 16, '26342665', NOW(), NOW()),
-- LUJAN DE LENIR NEKLE - 21182860
(8, 14, 'NEKLE', NOW(), NOW()),
(8, 15, 'LUJAN DE LENIR', NOW(), NOW()),
(8, 16, '21182860', NOW(), NOW()),
-- GIULIANA GUADALUPE TOLEDO FRETES - 56682249
(9, 14, 'TOLEDO FRETES', NOW(), NOW()),
(9, 15, 'GIULIANA GUADALUPE', NOW(), NOW()),
(9, 16, '56682249', NOW(), NOW()),
-- NOHA ALEJANDRO GIMENEZ - 58189124
(10, 14, 'GIMENEZ', NOW(), NOW()),
(10, 15, 'NOHA ALEJANDRO', NOW(), NOW()),
(10, 16, '58189124', NOW(), NOW()),
-- LUCIA AGUILERA - 23354188
(11, 14, 'AGUILERA', NOW(), NOW()),
(11, 15, 'LUCIA', NOW(), NOW()),
(11, 16, '23354188', NOW(), NOW()),
-- MAGALI LIZET BAEZ - 51307140
(12, 14, 'BAEZ', NOW(), NOW()),
(12, 15, 'MAGALI LIZET', NOW(), NOW()),
(12, 16, '51307140', NOW(), NOW()),
-- ROMEO NICOLAS ACOSTA - 55320853
(13, 14, 'ACOSTA', NOW(), NOW()),
(13, 15, 'ROMEO NICOLAS', NOW(), NOW()),
(13, 16, '55320853', NOW(), NOW()),
-- MARIA TOMASA DELGADO - 17554006
(14, 14, 'DELGADO', NOW(), NOW()),
(14, 15, 'MARIA TOMASA', NOW(), NOW()),
(14, 16, '17554006', NOW(), NOW()),
-- MAURICIO DORIANO CHAMORRO - 42614987
(15, 14, 'CHAMORRO', NOW(), NOW()),
(15, 15, 'MAURICIO DORIANO', NOW(), NOW()),
(15, 16, '42614987', NOW(), NOW()),
-- JOHANA ELIZABETH OCAMPO - 34477926
(16, 14, 'OCAMPO', NOW(), NOW()),
(16, 15, 'JOHANA ELIZABETH', NOW(), NOW()),
(16, 16, '34477926', NOW(), NOW()),
-- MIGUEL ALFREDO FRANCO - 20056381
(17, 14, 'FRANCO', NOW(), NOW()),
(17, 15, 'MIGUEL ALFREDO', NOW(), NOW()),
(17, 16, '20056381', NOW(), NOW()),
-- LEONARDO JAVIER CENTURION RIVEROS - 52992404
(18, 14, 'CENTURION RIVEROS', NOW(), NOW()),
(18, 15, 'LEONARDO JAVIER', NOW(), NOW()),
(18, 16, '52992404', NOW(), NOW()),
-- MARISELA CABAÑAS - 37718713
(19, 14, 'CABAÑAS', NOW(), NOW()),
(19, 15, 'MARISELA', NOW(), NOW()),
(19, 16, '37718713', NOW(), NOW());

-- 4. Resetear AUTO_INCREMENT
ALTER TABLE records AUTO_INCREMENT = 20;
ALTER TABLE record_data AUTO_INCREMENT = 62;

COMMIT;
