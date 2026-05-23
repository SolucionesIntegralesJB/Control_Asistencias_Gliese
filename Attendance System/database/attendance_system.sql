-- ============================================
-- Attendance System - Database Schema
-- Sistema de Control de Asistencias
-- Compatible con dashboard Gliese
-- ============================================

-- Tabla: attendance_shifts (Turnos de trabajo)
-- Almacena información de cada turno de trabajo diario
CREATE TABLE IF NOT EXISTS `attendance_shifts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL COMMENT 'ID del usuario (FK a user)',
  `job_role_id` INT NOT NULL COMMENT 'ID del rol/trabajo (FK a job_role)',
  `campus_id` INT NOT NULL COMMENT 'ID del campus (FK a campus)',
  `shift_date` DATE NOT NULL COMMENT 'Fecha del turno',
  
  -- Horarios programados
  `scheduled_start` TIME NOT NULL COMMENT 'Hora programada de inicio',
  `scheduled_end` TIME NOT NULL COMMENT 'Hora programada de fin (8 horas)',
  
  -- Horarios reales
  `actual_start` TIME NULL COMMENT 'Hora real de inicio (check-in)',
  `actual_end` TIME NULL COMMENT 'Hora real de fin (check-out)',
  
  -- Break (único por turno)
  `break_start` TIME NULL COMMENT 'Hora inicio del break',
  `break_end` TIME NULL COMMENT 'Hora fin del break',
  `break_duration` INT DEFAULT 0 COMMENT 'Duración del break en minutos',
  
  -- Cálculos de horas
  `total_worked_minutes` INT DEFAULT 0 COMMENT 'Total minutos trabajados',
  `regular_hours` DECIMAL(4,2) DEFAULT 0.00 COMMENT 'Horas regulares (max 8)',
  `overtime_hours` DECIMAL(4,2) DEFAULT 0.00 COMMENT 'Horas extra (excedente de 8)',
  
  -- Estado y metadatos
  `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending' COMMENT 'Estado del turno',
  `notes` TEXT NULL COMMENT 'Notas adicionales',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_shift_date` (`user_id`, `shift_date`) COMMENT 'Un turno por usuario por día',
  KEY `idx_user_id` (`user_id`),
  KEY `idx_shift_date` (`shift_date`),
  KEY `idx_status` (`status`),
  
  CONSTRAINT `fk_shifts_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shifts_job_role` FOREIGN KEY (`job_role_id`) REFERENCES `job_role` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_shifts_campus` FOREIGN KEY (`campus_id`) REFERENCES `campus` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Turnos de trabajo diarios';


-- Tabla: attendance_records (Registro de marcaciones)
-- Almacena cada marcación manual (botones)
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `shift_id` INT NOT NULL COMMENT 'ID del turno (FK a attendance_shifts)',
  `record_type` ENUM('check_in', 'check_out', 'break_start', 'break_end') NOT NULL COMMENT 'Tipo de marcación',
  `record_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp de la marcación',
  `location` VARCHAR(255) NULL COMMENT 'Ubicación (opcional)',
  `ip_address` VARCHAR(45) NULL COMMENT 'Dirección IP del usuario',
  `user_agent` VARCHAR(255) NULL COMMENT 'Navegador/dispositivo',
  `notes` TEXT NULL COMMENT 'Notas de la marcación',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_shift_id` (`shift_id`),
  KEY `idx_record_type` (`record_type`),
  KEY `idx_record_time` (`record_time`),
  
  CONSTRAINT `fk_records_shift` FOREIGN KEY (`shift_id`) REFERENCES `attendance_shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de marcaciones manuales';


-- Tabla: attendance_settings (Configuración del sistema)
-- Almacena configuraciones globales del sistema de asistencia
CREATE TABLE IF NOT EXISTS `attendance_settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Clave de configuración',
  `setting_value` TEXT NOT NULL COMMENT 'Valor de configuración',
  `description` VARCHAR(255) NULL COMMENT 'Descripción',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraciones del sistema de asistencia';


-- Insertar configuraciones por defecto
INSERT INTO `attendance_settings` (`setting_key`, `setting_value`, `description`) VALUES
('regular_hours_limit', '8', 'Límite de horas regulares por turno'),
('break_auto_duration', '30', 'Duración automática del break en minutos'),
('break_required', '1', 'Si el break es obligatorio (1=si, 0=no)'),
('overtime_calculation', '1', 'Si se calculan horas extra (1=si, 0=no)'),
('grace_period_minutes', '15', 'Período de gracia en minutos para check-in tardío')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;


-- ============================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================

-- Índices compuestos para consultas frecuentes
ALTER TABLE `attendance_shifts` 
ADD INDEX `idx_user_date_status` (`user_id`, `shift_date`, `status`),
ADD INDEX `idx_campus_date` (`campus_id`, `shift_date`);

ALTER TABLE `attendance_records`
ADD INDEX `idx_shift_type_time` (`shift_id`, `record_type`, `record_time`);


-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista: Resumen de turnos por usuario
CREATE OR REPLACE VIEW `v_shift_summary` AS
SELECT 
    s.id,
    s.user_id,
    u.first_name,
    u.last_name,
    s.job_role_id,
    jr.job_role,
    s.campus_id,
    c.description AS campus_name,
    s.shift_date,
    s.scheduled_start,
    s.scheduled_end,
    s.actual_start,
    s.actual_end,
    s.break_start,
    s.break_end,
    s.break_duration,
    s.total_worked_minutes,
    s.regular_hours,
    s.overtime_hours,
    s.status,
    s.notes
FROM attendance_shifts s
LEFT JOIN user u ON s.user_id = u.id
LEFT JOIN job_role jr ON s.job_role_id = jr.id
LEFT JOIN campus c ON s.campus_id = c.id;


-- Vista: Historial de marcaciones
CREATE OR REPLACE VIEW `v_attendance_history` AS
SELECT 
    r.id,
    r.shift_id,
    s.user_id,
    u.first_name,
    u.last_name,
    s.shift_date,
    r.record_type,
    r.record_time,
    r.location,
    r.ip_address,
    r.notes
FROM attendance_records r
LEFT JOIN attendance_shifts s ON r.shift_id = s.id
LEFT JOIN user u ON s.user_id = u.id
ORDER BY r.record_time DESC;
