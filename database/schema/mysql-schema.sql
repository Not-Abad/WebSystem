-- ============================================================================
-- Base de Datos: control_escolar
-- Institución: Escuela de Bachilleres "Lic. Antonio María de Rivera" (Vespertino)
-- Motor: MySQL 8.0 (InnoDB / utf8mb4_unicode_ci)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `control_escolar`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `control_escolar`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1. MÓDULO DE SEGURIDAD, ROLES Y AUDITORÍA
-- ----------------------------------------------------------------------------

CREATE TABLE `roles` (
  `id_rol` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uk_roles_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuarios` (
  `id_usuario` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_rol` BIGINT UNSIGNED NOT NULL,
  `username` VARCHAR(60) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `intentos_fallidos` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `bloqueado_hasta` DATETIME NULL,
  `ultimo_acceso` DATETIME NULL,
  `two_factor_hash` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuarios_username` (`username`),
  KEY `idx_usuarios_rol` (`id_rol`),
  CONSTRAINT `fk_usuarios_rol`
    FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bitacora_auditoria` (
  `id_bitacora` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` BIGINT UNSIGNED NOT NULL,
  `tabla_afectada` VARCHAR(60) NOT NULL,
  `id_registro_afectado` BIGINT UNSIGNED NULL,
  `accion` VARCHAR(40) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `ip_origen` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_bitacora`),
  KEY `idx_bitacora_usuario` (`id_usuario`),
  KEY `idx_bitacora_tabla_fecha` (`tabla_afectada`, `created_at`),
  CONSTRAINT `fk_bitacora_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. CATÁLOGOS DE ACTORES Y OFERTA EDUCATIVA BASE
-- ----------------------------------------------------------------------------

CREATE TABLE `puestos_administrativos` (
  `id_puesto` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_puesto`),
  UNIQUE KEY `uk_puestos_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `estados_alumno` (
  `id_estado_alumno` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id_estado_alumno`),
  UNIQUE KEY `uk_estados_alumno_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `planes_estudio` (
  `id_plan` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(30) NOT NULL,
  `nombre` VARCHAR(120) NOT NULL,
  `anio_inicio` YEAR NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_plan`),
  UNIQUE KEY `uk_planes_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `semestres` (
  `id_semestre` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `numero_semestre` TINYINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`id_semestre`),
  UNIQUE KEY `uk_semestres_numero` (`numero_semestre`),
  UNIQUE KEY `uk_semestres_nombre` (`nombre`),
  CONSTRAINT `chk_semestres_rango` CHECK (`numero_semestre` BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `periodos_academicos` (
  `id_periodo` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(20) NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_periodo`),
  UNIQUE KEY `uk_periodos_clave` (`clave`),
  CONSTRAINT `chk_periodos_fechas` CHECK (`fecha_fin` > `fecha_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `capacitaciones` (
  `id_capacitacion` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(80) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_capacitacion`),
  UNIQUE KEY `uk_capacitaciones_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `areas_propedeuticas` (
  `id_area_propedeutica` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_area_propedeutica`),
  UNIQUE KEY `uk_areas_propedeuticas_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. MÓDULO DE ACTORES INSTITUCIONALES
-- ----------------------------------------------------------------------------

CREATE TABLE `administradores` (
  `id_administrador` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` BIGINT UNSIGNED NOT NULL,
  `id_puesto` BIGINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `apellido_paterno` VARCHAR(60) NOT NULL,
  `apellido_materno` VARCHAR(60) NULL,
  `matricula_rfc` VARCHAR(20) NOT NULL,
  `correo_institucional` VARCHAR(120) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_administrador`),
  UNIQUE KEY `uk_administradores_usuario` (`id_usuario`),
  UNIQUE KEY `uk_administradores_matricula` (`matricula_rfc`),
  UNIQUE KEY `uk_administradores_correo` (`correo_institucional`),
  KEY `idx_administradores_puesto` (`id_puesto`),
  CONSTRAINT `fk_administradores_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_administradores_puesto`
    FOREIGN KEY (`id_puesto`) REFERENCES `puestos_administrativos` (`id_puesto`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `docentes` (
  `id_docente` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` BIGINT UNSIGNED NOT NULL,
  `id_capacitacion` BIGINT UNSIGNED NULL,
  `id_area_propedeutica` BIGINT UNSIGNED NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `apellido_paterno` VARCHAR(60) NOT NULL,
  `apellido_materno` VARCHAR(60) NULL,
  `matricula_rfc` VARCHAR(20) NOT NULL,
  `correo_institucional` VARCHAR(120) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_docente`),
  UNIQUE KEY `uk_docentes_usuario` (`id_usuario`),
  UNIQUE KEY `uk_docentes_matricula` (`matricula_rfc`),
  UNIQUE KEY `uk_docentes_correo` (`correo_institucional`),
  KEY `idx_docentes_capacitacion` (`id_capacitacion`),
  KEY `idx_docentes_area` (`id_area_propedeutica`),
  CONSTRAINT `fk_docentes_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_docentes_capacitacion`
    FOREIGN KEY (`id_capacitacion`) REFERENCES `capacitaciones` (`id_capacitacion`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_docentes_area`
    FOREIGN KEY (`id_area_propedeutica`) REFERENCES `areas_propedeuticas` (`id_area_propedeutica`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tutores` (
  `id_tutor` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` BIGINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `apellido_paterno` VARCHAR(60) NOT NULL,
  `apellido_materno` VARCHAR(60) NULL,
  `telefono` VARCHAR(15) NOT NULL,
  `correo` VARCHAR(120) NOT NULL,
  `ine_ruta` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tutor`),
  UNIQUE KEY `uk_tutores_usuario` (`id_usuario`),
  UNIQUE KEY `uk_tutores_correo` (`correo`),
  CONSTRAINT `fk_tutores_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `alumnos` (
  `id_alumno` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` BIGINT UNSIGNED NOT NULL,
  `id_tutor` BIGINT UNSIGNED NOT NULL,
  `id_estado_alumno` BIGINT UNSIGNED NOT NULL,
  `id_plan` BIGINT UNSIGNED NOT NULL,
  `id_semestre` BIGINT UNSIGNED NOT NULL,
  `id_capacitacion` BIGINT UNSIGNED NULL,
  `id_area_propedeutica` BIGINT UNSIGNED NULL,
  `matricula` VARCHAR(25) NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `apellido_paterno` VARCHAR(60) NOT NULL,
  `apellido_materno` VARCHAR(60) NULL,
  `curp` CHAR(18) NOT NULL,
  `acta_nacimiento_ruta` VARCHAR(255) NOT NULL,
  `boleta_secundaria_ruta` VARCHAR(255) NOT NULL,
  `correo` VARCHAR(120) NOT NULL,
  `promedio_general` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alumno`),
  UNIQUE KEY `uk_alumnos_usuario` (`id_usuario`),
  UNIQUE KEY `uk_alumnos_matricula` (`matricula`),
  UNIQUE KEY `uk_alumnos_curp` (`curp`),
  UNIQUE KEY `uk_alumnos_correo` (`correo`),
  KEY `idx_alumnos_tutor` (`id_tutor`),
  KEY `idx_alumnos_estado` (`id_estado_alumno`),
  KEY `idx_alumnos_plan` (`id_plan`),
  KEY `idx_alumnos_semestre` (`id_semestre`),
  KEY `idx_alumnos_capacitacion` (`id_capacitacion`),
  KEY `idx_alumnos_area` (`id_area_propedeutica`),
  CONSTRAINT `fk_alumnos_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_alumnos_tutor`
    FOREIGN KEY (`id_tutor`) REFERENCES `tutores` (`id_tutor`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_alumnos_estado`
    FOREIGN KEY (`id_estado_alumno`) REFERENCES `estados_alumno` (`id_estado_alumno`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_alumnos_plan`
    FOREIGN KEY (`id_plan`) REFERENCES `planes_estudio` (`id_plan`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_alumnos_semestre`
    FOREIGN KEY (`id_semestre`) REFERENCES `semestres` (`id_semestre`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_alumnos_capacitacion`
    FOREIGN KEY (`id_capacitacion`) REFERENCES `capacitaciones` (`id_capacitacion`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_alumnos_area`
    FOREIGN KEY (`id_area_propedeutica`) REFERENCES `areas_propedeuticas` (`id_area_propedeutica`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. MÓDULO DE MATERIAS, GRUPOS, CLASES Y HORARIOS
-- ----------------------------------------------------------------------------

CREATE TABLE `materias` (
  `id_materia` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_plan` BIGINT UNSIGNED NOT NULL,
  `id_semestre` BIGINT UNSIGNED NOT NULL,
  `id_capacitacion` BIGINT UNSIGNED NULL,
  `id_area_propedeutica` BIGINT UNSIGNED NULL,
  `clave` VARCHAR(25) NOT NULL,
  `nombre` VARCHAR(120) NOT NULL,
  `tipo_materia` VARCHAR(20) NOT NULL,
  `horas_semana` TINYINT UNSIGNED NOT NULL,
  `descripcion` TEXT NULL,
  `objetivo` TEXT NULL,
  `programa_pdf_ruta` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_materia`),
  UNIQUE KEY `uk_materias_clave` (`clave`),
  KEY `idx_materias_plan` (`id_plan`),
  KEY `idx_materias_semestre` (`id_semestre`),
  KEY `idx_materias_capacitacion` (`id_capacitacion`),
  KEY `idx_materias_area` (`id_area_propedeutica`),
  CONSTRAINT `chk_materias_tipo`
    CHECK (`tipo_materia` IN ('Basica', 'Paraescolar', 'Capacitacion', 'Propedeutica')),
  CONSTRAINT `fk_materias_plan`
    FOREIGN KEY (`id_plan`) REFERENCES `planes_estudio` (`id_plan`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_materias_semestre`
    FOREIGN KEY (`id_semestre`) REFERENCES `semestres` (`id_semestre`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_materias_capacitacion`
    FOREIGN KEY (`id_capacitacion`) REFERENCES `capacitaciones` (`id_capacitacion`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_materias_area`
    FOREIGN KEY (`id_area_propedeutica`) REFERENCES `areas_propedeuticas` (`id_area_propedeutica`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `grupos` (
  `id_grupo` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periodo` BIGINT UNSIGNED NOT NULL,
  `id_semestre` BIGINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(15) NOT NULL,
  `cupo_maximo` TINYINT UNSIGNED NOT NULL DEFAULT 45,
  PRIMARY KEY (`id_grupo`),
  UNIQUE KEY `uk_grupos_periodo_nombre` (`id_periodo`, `nombre`),
  KEY `idx_grupos_semestre` (`id_semestre`),
  CONSTRAINT `fk_grupos_periodo`
    FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_grupos_semestre`
    FOREIGN KEY (`id_semestre`) REFERENCES `semestres` (`id_semestre`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inscripciones_grupo` (
  `id_inscripcion` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_alumno` BIGINT UNSIGNED NOT NULL,
  `id_grupo` BIGINT UNSIGNED NOT NULL,
  `fecha_inscripcion` DATE NOT NULL,
  `estatus` VARCHAR(15) NOT NULL DEFAULT 'Inscrito',
  PRIMARY KEY (`id_inscripcion`),
  UNIQUE KEY `uk_inscripciones_alumno_grupo` (`id_alumno`, `id_grupo`),
  KEY `idx_inscripciones_grupo` (`id_grupo`),
  CONSTRAINT `chk_inscripciones_estatus`
    CHECK (`estatus` IN ('Inscrito', 'Baja')),
  CONSTRAINT `fk_inscripciones_alumno`
    FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_inscripciones_grupo`
    FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clases` (
  `id_clase` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_materia` BIGINT UNSIGNED NOT NULL,
  `id_docente` BIGINT UNSIGNED NOT NULL,
  `id_grupo` BIGINT UNSIGNED NOT NULL,
  `id_periodo` BIGINT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_clase`),
  UNIQUE KEY `uk_clases_materia_grupo_periodo` (`id_materia`, `id_grupo`, `id_periodo`),
  KEY `idx_clases_docente` (`id_docente`),
  KEY `idx_clases_grupo` (`id_grupo`),
  KEY `idx_clases_periodo` (`id_periodo`),
  CONSTRAINT `fk_clases_materia`
    FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_clases_docente`
    FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_clases_grupo`
    FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_clases_periodo`
    FOREIGN KEY (`id_periodo`) REFERENCES `periodos_academicos` (`id_periodo`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `horarios` (
  `id_horario` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_clase` BIGINT UNSIGNED NOT NULL,
  `dia_semana` VARCHAR(15) NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  `aula` VARCHAR(30) NULL,
  PRIMARY KEY (`id_horario`),
  KEY `idx_horarios_clase` (`id_clase`),
  KEY `idx_horarios_dia_horas` (`dia_semana`, `hora_inicio`, `hora_fin`),
  CONSTRAINT `chk_horarios_dia`
    CHECK (`dia_semana` IN ('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes')),
  CONSTRAINT `chk_horarios_horas`
    CHECK (`hora_fin` > `hora_inicio`),
  CONSTRAINT `fk_horarios_clase`
    FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. MÓDULO DE EVALUACIÓN Y CALIFICACIONES
-- ----------------------------------------------------------------------------

CREATE TABLE `criterios_evaluacion` (
  `id_criterio` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_clase` BIGINT UNSIGNED NOT NULL,
  `parcial` TINYINT UNSIGNED NOT NULL,
  `nombre_rubro` VARCHAR(60) NOT NULL,
  `porcentaje` DECIMAL(5,2) NOT NULL,
  PRIMARY KEY (`id_criterio`),
  KEY `idx_criterios_clase_parcial` (`id_clase`, `parcial`),
  CONSTRAINT `chk_criterios_parcial`
    CHECK (`parcial` IN (1, 2, 3)),
  CONSTRAINT `chk_criterios_porcentaje`
    CHECK (`porcentaje` > 0.00 AND `porcentaje` <= 100.00),
  CONSTRAINT `fk_criterios_clase`
    FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id_clase`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `calificaciones` (
  `id_calificacion` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_criterio` BIGINT UNSIGNED NOT NULL,
  `id_alumno` BIGINT UNSIGNED NOT NULL,
  `puntaje_obtenido` DECIMAL(5,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_calificacion`),
  UNIQUE KEY `uk_calificaciones_criterio_alumno` (`id_criterio`, `id_alumno`),
  KEY `idx_calificaciones_alumno` (`id_alumno`),
  CONSTRAINT `chk_calificaciones_puntaje`
    CHECK (`puntaje_obtenido` >= 0.00 AND `puntaje_obtenido` <= 10.00),
  CONSTRAINT `fk_calificaciones_criterio`
    FOREIGN KEY (`id_criterio`) REFERENCES `criterios_evaluacion` (`id_criterio`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_calificaciones_alumno`
    FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. MÓDULO DE GESTIÓN DE CONTENIDO WEB (CMS)
-- ----------------------------------------------------------------------------

CREATE TABLE `noticias` (
  `id_noticia` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_administrador` BIGINT UNSIGNED NOT NULL,
  `titulo` VARCHAR(160) NOT NULL,
  `contenido` TEXT NOT NULL,
  `imagen_portada_ruta` VARCHAR(255) NULL,
  `fecha_publicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_noticia`),
  KEY `idx_noticias_admin` (`id_administrador`),
  CONSTRAINT `fk_noticias_administrador`
    FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documentos` (
  `id_documento` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_administrador` BIGINT UNSIGNED NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `categoria` VARCHAR(60) NOT NULL,
  `archivo_ruta` VARCHAR(255) NOT NULL,
  `fecha_subida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_documento`),
  KEY `idx_documentos_admin` (`id_administrador`),
  CONSTRAINT `fk_documentos_administrador`
    FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reportes_financieros` (
  `id_reporte` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_administrador` BIGINT UNSIGNED NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `mes` TINYINT UNSIGNED NOT NULL,
  `anio` YEAR NOT NULL,
  `archivo_pdf_ruta` VARCHAR(255) NOT NULL,
  `fecha_publicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reporte`),
  UNIQUE KEY `uk_reportes_mes_anio` (`mes`, `anio`),
  KEY `idx_reportes_admin` (`id_administrador`),
  CONSTRAINT `chk_reportes_mes`
    CHECK (`mes` BETWEEN 1 AND 12),
  CONSTRAINT `fk_reportes_administrador`
    FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `imagenes_galeria` (
  `id_imagen` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_administrador` BIGINT UNSIGNED NOT NULL,
  `titulo` VARCHAR(120) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `imagen_ruta` VARCHAR(255) NOT NULL,
  `fecha_subida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_imagen`),
  KEY `idx_galeria_admin` (`id_administrador`),
  CONSTRAINT `fk_galeria_administrador`
    FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 7. DATOS SEMILLA DE CATÁLOGOS FIJOS DEL DOMINIO
-- ----------------------------------------------------------------------------

INSERT IGNORE INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'SuperUsuario'),
(2, 'Administrador'),
(3, 'Docente'),
(4, 'Alumno'),
(5, 'Tutor');

INSERT IGNORE INTO `puestos_administrativos` (`id_puesto`, `nombre`) VALUES
(1, 'secretaria'),
(2, 'RRHH'),
(3, 'subdirector'),
(4, 'director');

INSERT IGNORE INTO `estados_alumno` (`id_estado_alumno`, `nombre`) VALUES
(1, 'preinscrito'),
(2, 'activo'),
(3, 'baja temporal'),
(4, 'baja definitiva'),
(5, 'graduado');

INSERT IGNORE INTO `semestres` (`id_semestre`, `numero_semestre`, `nombre`) VALUES
(1, 1, 'Primer Semestre'),
(2, 2, 'Segundo Semestre'),
(3, 3, 'Tercer Semestre'),
(4, 4, 'Cuarto Semestre'),
(5, 5, 'Quinto Semestre'),
(6, 6, 'Sexto Semestre');

INSERT IGNORE INTO `capacitaciones` (`id_capacitacion`, `nombre`, `is_active`) VALUES
(1, 'Informática', 1),
(2, 'Turismo', 1),
(3, 'Emprendimiento', 1),
(4, 'Contabilidad', 1);