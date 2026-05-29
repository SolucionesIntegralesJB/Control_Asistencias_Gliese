-- ============================================
-- Migration: Financial Fields for Attendance System
-- Migración: Campos financieros para el módulo Asistencias
-- ============================================

-- Paso 1: Agregar campos financieros a attendance_shifts
ALTER TABLE `attendance_shifts`
  ADD COLUMN `hourly_rate` DECIMAL(10,2) DEFAULT NULL COMMENT 'Tarifa por hora regular' AFTER `overtime_hours`,
  ADD COLUMN `overtime_rate` DECIMAL(10,2) DEFAULT NULL COMMENT 'Tarifa por hora extra' AFTER `hourly_rate`,
  ADD COLUMN `regular_payment` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Pago regular calculado (regular_hours * hourly_rate)' AFTER `overtime_rate`,
  ADD COLUMN `overtime_payment` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Pago extra calculado (overtime_hours * overtime_rate)' AFTER `regular_payment`,
  ADD COLUMN `total_payment` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Pago total (regular_payment + overtime_payment)' AFTER `overtime_payment`,
  ADD COLUMN `payment_calculated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp del último cálculo de pago' AFTER `total_payment`;

-- Paso 2: Crear tabla attendance_shift_events para timeline y auditoría
CREATE TABLE IF NOT EXISTS `attendance_shift_events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `shift_id` INT NOT NULL COMMENT 'ID del turno (FK a attendance_shifts)',
  `event_type` ENUM('shift_start', 'break_start', 'break_end', 'shift_end', 'manual_edit', 'rate_change') NOT NULL COMMENT 'Tipo de evento',
  `event_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp del evento',
  `event_data` JSON NULL COMMENT 'Datos adicionales del evento (valores anteriores/nuevos)',
  `created_by` INT NULL COMMENT 'ID del usuario que realizó el evento (opcional)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_shift_id` (`shift_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_event_time` (`event_time`),
  
  CONSTRAINT `fk_events_shift` FOREIGN KEY (`shift_id`) REFERENCES `attendance_shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Eventos de timeline y auditoría de turnos';

-- Paso 3: Actualizar vista v_shift_summary para incluir campos financieros
DROP VIEW IF EXISTS `v_shift_summary`;
CREATE OR REPLACE VIEW `v_shift_summary` AS
SELECT 
    s.id,
    s.employee_id,
    e.name AS employee_name,
    e.email AS employee_email,
    e.position,
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
    s.hourly_rate,
    s.overtime_rate,
    s.regular_payment,
    s.overtime_payment,
    s.total_payment,
    s.payment_calculated_at,
    s.status,
    s.notes
FROM attendance_shifts s
LEFT JOIN employees e ON s.employee_id = e.id
LEFT JOIN job_role jr ON s.job_role_id = jr.id
LEFT JOIN campus c ON s.campus_id = c.id;

-- Paso 4: Crear índices adicionales para optimizar consultas financieras
ALTER TABLE `attendance_shifts`
ADD INDEX `idx_payment_calculated` (`payment_calculated_at`),
ADD INDEX `idx_total_payment` (`total_payment`);

-- ============================================
-- Explicación de cambios
-- ============================================
--
-- 1. Campos financieros en attendance_shifts:
--    - hourly_rate: Tarifa por hora regular (ingresada manualmente)
--    - overtime_rate: Tarifa por hora extra (ingresada manualmente)
--    - regular_payment: Pago regular calculado (regular_hours * hourly_rate)
--    - overtime_payment: Pago extra calculado (overtime_hours * overtime_rate)
--    - total_payment: Pago total (regular_payment + overtime_payment)
--    - payment_calculated_at: Timestamp del último cálculo (para snapshot histórico)
--
-- 2. Tabla attendance_shift_events:
--    - Permite tracking de timeline profesional
--    - Auditoría de cambios manuales en turnos
--    - Separa concerns: marcaciones (attendance_records) vs eventos de timeline
--    - event_data JSON permite almacenar información flexible
--
-- 3. Vista actualizada:
--    - Incluye nuevos campos financieros
--    - Facilita consultas de reportes financieros
--
-- 4. Índices adicionales:
--    - Optimizan consultas por fecha de cálculo y monto de pago
--
-- ============================================
