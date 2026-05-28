-- ============================================
-- Migration: Attendance System - user -> employees (SAFE VERSION)
-- Migración de autenticación de tabla user a employees
-- Este script es seguro y maneja múltiples escenarios
-- ============================================

-- ============================================
-- VERIFICACIÓN PREVIA
-- ============================================

-- Verificar si la tabla attendance_shifts existe
SELECT 'Verificando tabla attendance_shifts...' AS status;

-- Verificar si la columna actual es user_id o employee_id
-- Esto se hace dinámicamente en el script

-- ============================================
-- PASO 1: Eliminar foreign keys existentes (si existen)
-- ============================================

-- Intentar eliminar fk_shifts_user si existe
SET @drop_fk_user = (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND CONSTRAINT_NAME = 'fk_shifts_user'
);

SET @sql_fk_user = IF(@drop_fk_user > 0,
    'ALTER TABLE attendance_shifts DROP FOREIGN KEY fk_shifts_user',
    'SELECT ''fk_shifts_user no existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_fk_user;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Intentar eliminar fk_shifts_employee si existe (por si acaso)
SET @drop_fk_employee = (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND CONSTRAINT_NAME = 'fk_shifts_employee'
);

SET @sql_fk_employee = IF(@drop_fk_employee > 0,
    'ALTER TABLE attendance_shifts DROP FOREIGN KEY fk_shifts_employee',
    'SELECT ''fk_shifts_employee no existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_fk_employee;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 2: Renombrar columna user_id a employee_id (si es necesario)
-- ============================================

SET @has_user_id = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND COLUMN_NAME = 'user_id'
);

SET @has_employee_id = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND COLUMN_NAME = 'employee_id'
);

-- Si tiene user_id, renombrar a employee_id
SET @sql_rename = IF(@has_user_id > 0,
    'ALTER TABLE attendance_shifts CHANGE COLUMN user_id employee_id BIGINT NOT NULL COMMENT ''ID del empleado (FK a employees.id - BIGINT SIGNED)''',
    'SELECT ''La columna ya es employee_id, omitiendo renombrado...'' AS status'
);
PREPARE stmt FROM @sql_rename;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Si ya tiene employee_id pero es INT, cambiar a BIGINT
SET @sql_type = IF(@has_employee_id > 0 AND @has_user_id = 0,
    'ALTER TABLE attendance_shifts MODIFY COLUMN employee_id BIGINT NOT NULL COMMENT ''ID del empleado (FK a employees.id - BIGINT SIGNED)''',
    'SELECT ''Tipo de columna ya correcto o ya fue cambiado, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_type;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 3: Crear foreign key a employees
-- ============================================

-- Verificar si ya existe el FK
SET @has_fk = (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND CONSTRAINT_NAME = 'fk_shifts_employee'
    AND REFERENCED_TABLE_NAME = 'employees'
);

SET @sql_create_fk = IF(@has_fk = 0,
    'ALTER TABLE attendance_shifts ADD CONSTRAINT fk_shifts_employee FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE',
    'SELECT ''fk_shifts_employee ya existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_create_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 4: Actualizar índices (eliminar y recrear)
-- ============================================

-- Eliminar idx_user_id si existe
SET @drop_idx_user = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND INDEX_NAME = 'idx_user_id'
);

SET @sql_drop_idx_user = IF(@drop_idx_user > 0,
    'ALTER TABLE attendance_shifts DROP INDEX idx_user_id',
    'SELECT ''idx_user_id no existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_drop_idx_user;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear idx_employee_id si no existe
SET @has_idx_employee = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND INDEX_NAME = 'idx_employee_id'
);

SET @sql_create_idx_employee = IF(@has_idx_employee = 0,
    'ALTER TABLE attendance_shifts ADD INDEX idx_employee_id (employee_id)',
    'SELECT ''idx_employee_id ya existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_create_idx_employee;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 5: Actualizar índice compuesto unique_user_shift_date
-- ============================================

-- Eliminar unique_user_shift_date si existe
SET @drop_unique_user = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND INDEX_NAME = 'unique_user_shift_date'
);

SET @sql_drop_unique_user = IF(@drop_unique_user > 0,
    'ALTER TABLE attendance_shifts DROP INDEX unique_user_shift_date',
    'SELECT ''unique_user_shift_date no existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_drop_unique_user;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear unique_employee_shift_date si no existe
SET @has_unique_employee = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND INDEX_NAME = 'unique_employee_shift_date'
);

SET @sql_create_unique_employee = IF(@has_unique_employee = 0,
    'ALTER TABLE attendance_shifts ADD UNIQUE KEY unique_employee_shift_date (employee_id, shift_date) COMMENT ''Un turno por empleado por día''',
    'SELECT ''unique_employee_shift_date ya existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_create_unique_employee;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 6: Actualizar índice compuesto idx_user_date_status
-- ============================================

-- Eliminar idx_user_date_status si existe
SET @drop_idx_user_date = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND INDEX_NAME = 'idx_user_date_status'
);

SET @sql_drop_idx_user_date = IF(@drop_idx_user_date > 0,
    'ALTER TABLE attendance_shifts DROP INDEX idx_user_date_status',
    'SELECT ''idx_user_date_status no existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_drop_idx_user_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear idx_employee_date_status si no existe
SET @has_idx_employee_date = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'attendance_shifts'
    AND INDEX_NAME = 'idx_employee_date_status'
);

SET @sql_create_idx_employee_date = IF(@has_idx_employee_date = 0,
    'ALTER TABLE attendance_shifts ADD INDEX idx_employee_date_status (employee_id, shift_date, status)',
    'SELECT ''idx_employee_date_status ya existe, omitiendo...'' AS status'
);
PREPARE stmt FROM @sql_create_idx_employee_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 7: Actualizar vista v_shift_summary
-- ============================================

DROP VIEW IF EXISTS v_shift_summary;

CREATE OR REPLACE VIEW v_shift_summary AS
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

-- ============================================
-- PASO 8: Actualizar vista v_attendance_history
-- ============================================

DROP VIEW IF EXISTS v_attendance_history;

CREATE OR REPLACE VIEW v_attendance_history AS
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
-- VERIFICACIÓN FINAL
-- ============================================

SELECT '============================================' AS '';
SELECT 'MIGRACIÓN COMPLETADA' AS status;
SELECT '============================================' AS '';

-- Verificar estructura final
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    EXTRA,
    COLUMN_COMMENT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'attendance_shifts'
AND COLUMN_NAME = 'employee_id';

-- Verificar foreign key
SELECT
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'attendance_shifts'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Verificar índices
SELECT
    INDEX_NAME,
    COLUMN_NAME,
    NON_UNIQUE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'attendance_shifts'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

SELECT '============================================' AS '';
SELECT 'Verificación completada exitosamente' AS status;
SELECT '============================================' AS '';
