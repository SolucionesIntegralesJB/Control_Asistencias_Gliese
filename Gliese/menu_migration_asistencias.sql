-- ============================================
-- Migration: Menu and Sub-menu for Asistencias Module
-- Migración: Menú y Sub-menú para el módulo Asistencias
-- ============================================

-- Paso 1: Insertar nuevo menú "Control de Asistencias"
INSERT INTO `menu` (`id`, `description`, `icon`, `order`) VALUES
(14, 'Control de Asistencias', 'clock', 14);

-- Paso 2: Insertar sub-menú "Asistencias"
INSERT INTO `sub_menu` (`id`, `id_menu`, `description`, `icon`, `url`, `order`) VALUES
(47, 14, 'Asistencias', 'circle', 'Attendance', 1);

-- Paso 3: Insertar permisos para el rol 1 (administrador)
-- Nota: Ajustar id_role según los roles existentes en el sistema
INSERT INTO `permission` (`id`, `id_role`, `id_sub_menu`, `status`) VALUES
(100, 1, 47, 1);

-- ============================================
-- Explicación de cambios
-- ============================================
--
-- 1. Nuevo menú "Control de Asistencias":
--    - ID: 14 (siguiente disponible)
--    - Icon: 'clock' (relacionado con tiempo/asistencia)
--    - Order: 14 (al final de la lista de menús)
--
-- 2. Sub-menú "Asistencias":
--    - ID: 47 (siguiente disponible)
--    - id_menu: 14 (vinculado al menú Control de Asistencias)
--    - URL: 'Attendance' (controlador C_Attendance)
--    - Order: 1 (primer y único sub-menú por ahora)
--
-- 3. Permisos:
--    - Se agrega permiso para el rol 1 (administrador)
--    - Ajustar id_role según los roles existentes en el sistema
--    - Se puede agregar más permisos para otros roles si es necesario
--
-- ============================================
