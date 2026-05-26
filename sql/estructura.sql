-- ================================================================
-- Technical del Perú — Estructura de Base de Datos
-- Versión: 1.0.0
-- Motor: MySQL 8+ / InnoDB
-- Charset: utf8mb4_unicode_ci
-- Fecha: 2026-05-26
-- ================================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `technical_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `technical_db`;

-- ================================================================
-- TABLA: roles
-- Descripción: Roles del sistema (datos fijos)
-- ================================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles predefinidos
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
  (1, 'Administrador', 'Acceso total al sistema'),
  (2, 'Vendedor', 'Gestión de certificados y productos'),
  (3, 'Marketing', 'Gestión de banners, blog y contenido'),
  (4, 'Gestor de contenido', 'Edición de secciones y textos del sitio')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `descripcion` = VALUES(`descripcion`);

-- ================================================================
-- TABLA: usuarios
-- Descripción: Usuarios del panel de administración
-- ================================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol_id` INT UNSIGNED NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario administrador inicial
-- Email: admin@technicaldelperu.pe
-- Contraseña: Admin2026! (CAMBIAR EN PRODUCCIÓN)
INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol_id`) VALUES
  ('Administrador', 'admin@technicaldelperu.pe', '$2y$10$xRINKBH29H.c2Cz1jZfH9.vRLZRLVSzkw/2MmfdCC5X4Drvndj2I2', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- ================================================================
-- TABLA: config_firma
-- Descripción: Configuración de firma digital (una sola fila)
-- ================================================================
CREATE TABLE IF NOT EXISTS `config_firma` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre_firmante` VARCHAR(150) NOT NULL DEFAULT '',
  `cargo` VARCHAR(150) NOT NULL DEFAULT '',
  `ruta_imagen` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fila inicial de configuración de firma
INSERT INTO `config_firma` (`id`, `nombre_firmante`, `cargo`) VALUES
  (1, '', '')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ================================================================
-- TABLA: certificados
-- Descripción: Certificados emitidos (módulo estrella)
-- ================================================================
CREATE TABLE IF NOT EXISTS `certificados` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ruc` CHAR(11) NOT NULL,
  `razon_social` VARCHAR(255) NOT NULL,
  `nombre_participante` VARCHAR(200) NOT NULL,
  `tipo_certificado` VARCHAR(100) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `fecha_emision` DATE NOT NULL,
  `fecha_vencimiento` DATE DEFAULT NULL,
  `codigo_verificacion` VARCHAR(64) NOT NULL UNIQUE,
  `ruta_qr` VARCHAR(500) DEFAULT NULL,
  `estado` ENUM('vigente', 'vencido', 'anulado') NOT NULL DEFAULT 'vigente',
  `creado_por` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ruc` (`ruc`),
  INDEX `idx_codigo` (`codigo_verificacion`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `fk_certificados_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLA: productos
-- Descripción: Catálogo de productos
-- ================================================================
CREATE TABLE IF NOT EXISTS `productos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `descripcion_corta` VARCHAR(500) DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `industria` VARCHAR(100) DEFAULT NULL,
  `imagen_principal` VARCHAR(500) DEFAULT NULL,
  `modelo_3d` VARCHAR(500) DEFAULT NULL COMMENT 'Ruta al archivo .glb',
  `precio_referencial` DECIMAL(10,2) DEFAULT NULL,
  `destacado` TINYINT(1) NOT NULL DEFAULT 0,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `orden` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_industria` (`industria`),
  INDEX `idx_destacado` (`destacado`),
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLA: banners
-- Descripción: Banners del homepage (slider)
-- ================================================================
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(200) DEFAULT NULL,
  `subtitulo` VARCHAR(300) DEFAULT NULL,
  `imagen` VARCHAR(500) NOT NULL,
  `enlace` VARCHAR(500) DEFAULT NULL,
  `orden` INT NOT NULL DEFAULT 0,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLA: blog
-- Descripción: Artículos del blog
-- ================================================================
CREATE TABLE IF NOT EXISTS `blog` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL COMMENT 'Meta description para SEO',
  `contenido` LONGTEXT NOT NULL,
  `imagen_portada` VARCHAR(500) DEFAULT NULL,
  `autor` VARCHAR(150) DEFAULT NULL,
  `tags` VARCHAR(500) DEFAULT NULL,
  `publicado` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_publicacion` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_publicado` (`publicado`),
  INDEX `idx_fecha` (`fecha_publicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLA: secciones
-- Descripción: Textos editables del sitio (misión, visión, etc.)
-- ================================================================
CREATE TABLE IF NOT EXISTS `secciones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clave` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Identificador único: mision, vision, quienes_somos, etc.',
  `titulo` VARCHAR(200) DEFAULT NULL,
  `contenido` LONGTEXT DEFAULT NULL,
  `imagen` VARCHAR(500) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Secciones predefinidas
INSERT INTO `secciones` (`clave`, `titulo`, `contenido`) VALUES
  ('mision', 'Misión', 'Brindar soluciones técnicas de alta calidad para la industria peruana.'),
  ('vision', 'Visión', 'Ser la empresa líder en servicios técnicos especializados del Perú.'),
  ('quienes_somos', 'Quiénes Somos', 'Technical del Perú es una empresa especializada en soluciones industriales.'),
  ('valores', 'Valores', 'Compromiso, innovación, seguridad y excelencia.')
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`), `contenido` = VALUES(`contenido`);

-- ================================================================
-- TABLA: contactos
-- Descripción: Mensajes del formulario de contacto
-- ================================================================
CREATE TABLE IF NOT EXISTS `contactos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(150) NOT NULL,
  `email` VARCHAR(200) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `asunto` VARCHAR(255) DEFAULT NULL,
  `mensaje` TEXT NOT NULL,
  `leido` TINYINT(1) NOT NULL DEFAULT 0,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- TABLA: logs
-- Descripción: Registro de actividad del sistema
-- ================================================================
CREATE TABLE IF NOT EXISTS `logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `accion` VARCHAR(255) NOT NULL,
  `detalle` TEXT DEFAULT NULL,
  `tabla_afectada` VARCHAR(100) DEFAULT NULL,
  `registro_id` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
