-- ============================================
-- Migration: Attendance System - user -> employees
-- Migración de autenticación de tabla user a employees
-- ============================================

-- Paso 1: Eliminar foreign key existente en attendance_shifts
ALTER TABLE `attendance_shifts` DROP FOREIGN KEY `fk_shifts_user`;

-- Paso 2: Renombrar columna user_id a employee_id y cambiar tipo a BIGINT para coincidir con employees.id
ALTER TABLE `attendance_shifts` CHANGE COLUMN `user_id` `employee_id` BIGINT NOT NULL COMMENT 'ID del empleado (FK a employees)';

-- Paso 3: Crear nuevo foreign key a employees (BIGINT)
ALTER TABLE `attendance_shifts`
  ADD CONSTRAINT `fk_shifts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

-- Paso 4: Actualizar índices que usaban user_id
ALTER TABLE `attendance_shifts` DROP INDEX `idx_user_id`;
ALTER TABLE `attendance_shifts` ADD INDEX `idx_employee_id` (`employee_id`);

-- Paso 5: Actualizar índice compuesto unique_user_shift_date
ALTER TABLE `attendance_shifts` DROP INDEX `unique_user_shift_date`;
ALTER TABLE `attendance_shifts` ADD UNIQUE KEY `unique_employee_shift_date` (`employee_id`, `shift_date`) COMMENT 'Un turno por empleado por día';

-- Paso 6: Actualizar índice compuesto idx_user_date_status
ALTER TABLE `attendance_shifts` DROP INDEX `idx_user_date_status`;
ALTER TABLE `attendance_shifts` ADD INDEX `idx_employee_date_status` (`employee_id`, `shift_date`, `status`);

-- Paso 7: Actualizar vista v_shift_summary
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
    s.status,
    s.notes
FROM attendance_shifts s
LEFT JOIN employees e ON s.employee_id = e.id
LEFT JOIN job_role jr ON s.job_role_id = jr.id
LEFT JOIN campus c ON s.campus_id = c.id;

-- Paso 8: Actualizar vista v_attendance_history
DROP VIEW IF EXISTS `v_attendance_history`;
CREATE OR REPLACE VIEW `v_attendance_history` AS
SELECT 
    r.id,
    r.shift_id,
    s.employee_id,
    e.name AS employee_name,
    e.email AS employee_email,
    s.shift_date,
    r.record_type,
    r.record_time,
    r.location,
    r.ip_address,
    r.notes
FROM attendance_records r
LEFT JOIN attendance_shifts s ON r.shift_id = s.id
LEFT JOIN employees e ON s.employee_id = e.id
ORDER BY r.record_time DESC;

-- ============================================
-- Explicación de cambios
-- ============================================
--
-- 1. FK fk_shifts_user -> fk_shifts_employee:
--    Cambia la referencia de user.id a employees.id
--
-- 2. Columna user_id -> employee_id:
--    Renombra para consistencia con nueva entidad
--    **IMPORTANTE**: Cambia tipo de INT a BIGINT para coincidir con employees.id (BIGINT NOT NULL)
--    Esto corrige el error #3780 - Referencing column 'employee_id' and referenced column 'id' are incompatible
--
-- 3. Índices actualizados:
--    idx_user_id -> idx_employee_id
--    unique_user_shift_date -> unique_employee_shift_date
--    idx_user_date_status -> idx_employee_date_status
--
-- 4. Vistas actualizadas:
--    v_shift_summary: usa employees.name en lugar de user.first_name + user.last_name
--    v_attendance_history: usa employees.name en lugar de user.first_name + user.last_name
--    Agrega campos adicionales de employees (email, position)
--
-- ============================================
