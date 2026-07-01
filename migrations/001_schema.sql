-- COPRODIS - Esquema de Base de Datos
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS coprodis
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE coprodis;

-- ============================================
-- TABLAS DE SEGURIDAD Y AUTENTICACIÓN
-- ============================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(8) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefono VARCHAR(50),
    localidad VARCHAR(150),
    password VARCHAR(255) NOT NULL,
    password_temporal BOOLEAN DEFAULT FALSE,
    password_changed_at TIMESTAMP NULL,
    rol_id INT NOT NULL,
    estado ENUM('activo', 'inactivo', 'bloqueado') DEFAULT 'activo',
    ultimo_acceso TIMESTAMP NULL,
    ip_acceso VARCHAR(45),
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    remember_token VARCHAR(255),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_users_email (email),
    INDEX idx_users_dni (dni),
    INDEX idx_users_estado (estado),
    INDEX idx_users_rol (rol_id),
    INDEX idx_users_deleted (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expira_en TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_token (token),
    INDEX idx_password_resets_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE FORMULARIOS DINÁMICOS
-- ============================================

CREATE TABLE forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    estado ENUM('borrador', 'publicado', 'despublicado') DEFAULT 'borrador',
    created_by INT NOT NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_forms_estado (estado),
    INDEX idx_forms_created_by (created_by),
    INDEX idx_forms_deleted (deleted_at)
) ENGINE=InnoDB;

CREATE TABLE form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    etiqueta VARCHAR(255) NOT NULL,
    placeholder VARCHAR(255),
    ayuda VARCHAR(255),
    requerido BOOLEAN DEFAULT FALSE,
    opciones JSON,
    valor_defecto VARCHAR(255),
    orden INT DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    INDEX idx_form_fields_form (form_id),
    INDEX idx_form_fields_orden (orden),
    INDEX idx_form_fields_deleted (deleted_at)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE REGISTROS
-- ============================================

CREATE TABLE records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    user_id INT NOT NULL,
    estado ENUM('activo', 'archivado') DEFAULT 'activo',
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_records_form (form_id),
    INDEX idx_records_user (user_id),
    INDEX idx_records_estado (estado),
    INDEX idx_records_deleted (deleted_at),
    INDEX idx_records_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE record_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    field_id INT NOT NULL,
    valor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES records(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES form_fields(id) ON DELETE RESTRICT,
    INDEX idx_record_data_record (record_id),
    INDEX idx_record_data_field (field_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE HISTORIAL Y AUDITORÍA
-- ============================================

CREATE TABLE record_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    field_id INT NULL,
    user_id INT NOT NULL,
    valor_anterior TEXT,
    valor_nuevo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES records(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES form_fields(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_record_changes_record (record_id),
    INDEX idx_record_changes_user (user_id),
    INDEX idx_record_changes_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    descripcion TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audits_user (user_id),
    INDEX idx_audits_accion (accion),
    INDEX idx_audits_modulo (modulo),
    INDEX idx_audits_created (created_at),
    INDEX idx_audits_ip (ip)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE NOTIFICACIONES
-- ============================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT,
    tipo VARCHAR(50) DEFAULT 'info',
    leido BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_leido (leido),
    INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- TABLAS DE ARCHIVOS ADJUNTOS
-- ============================================

CREATE TABLE attached_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    field_id INT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    tipo VARCHAR(100),
    tamaño INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES records(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES form_fields(id) ON DELETE SET NULL,
    INDEX idx_attached_files_record (record_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLA DE CONFIGURACIONES
-- ============================================

CREATE TABLE configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_clave (clave)
) ENGINE=InnoDB;

-- ============================================
-- DATOS INICIALES
-- ============================================

INSERT INTO roles (nombre, slug, descripcion) VALUES
    ('Super Usuario', 'super_usuario', 'Acceso total al sistema'),
    ('Administrador', 'administrador', 'Administración operativa del sistema'),
    ('Usuario', 'usuario', 'Acceso básico a funciones operativas');

-- Password: Coprodis2024!
INSERT INTO users (apellido, nombre, dni, email, telefono, localidad, password, password_temporal, rol_id, estado)
VALUES (
    'Administrador',
    'COPRODIS',
    '12345678',
    'admin@coprodis.com',
    '3764000000',
    'Posadas',
    '$2y$12$wbM0TsTdeyq4vEuWMY.h9.OzUPlmnxpoap7wW3X5UZYsyug2mMT/O',
    TRUE,
    1,
    'activo'
);

INSERT INTO configuraciones (clave, valor) VALUES
    ('nombre_sistema', 'COPRODIS'),
    ('logo', ''),
    ('zona_horaria', 'America/Argentina/Buenos_Aires'),
    ('registros_por_pagina', '25'),
    ('smtp_host', 'smtp.gmail.com'),
    ('smtp_port', '587'),
    ('smtp_encryption', 'tls'),
    ('smtp_user', 'noreplymisiones@gmail.com'),
    ('smtp_pass', 'mxez mnap qysx rucy'),
    ('smtp_from_email', 'noreplymisiones@gmail.com'),
    ('smtp_from_name', 'CO.PRO.DIS');
