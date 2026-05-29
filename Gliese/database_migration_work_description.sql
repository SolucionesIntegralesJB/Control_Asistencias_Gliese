-- ============================================
-- Migration: Work Description for Attendance System
-- Migración: Campo de descripción de trabajo para el módulo Asistencias
-- ============================================

-- Paso 1: Agregar campo work_description a attendance_shifts
ALTER TABLE `attendance_shifts`
  ADD COLUMN `work_description` TEXT NULL COMMENT 'Descripción del trabajo a realizar durante el turno (ingresada al iniciar turno)' AFTER `notes`;

-- ============================================
-- Explicación de cambios
-- ============================================
--
-- 1. Campo work_description en attendance_shifts:
--    - TEXT NULL: Permite descripciones largas (hasta 65,535 caracteres)
--    - Ubicado después de `notes` para mantener agrupación lógica de campos de texto
--    - NULL por defecto: Compatible con registros existentes
--    - Comentario claro: Indica propósito y cuándo se ingresa
--
-- 2. Por qué esta estructura (Opción A):
--    - La descripción del trabajo es parte intrínseca del turno
--    - No requiere entidad separada (no es una relación 1:N)
--    - Mantiene compatibilidad con sistema actual
--    - Facilita consultas y reportes futuros
--    - Sigue patrón de diseño actual (datos del turno en una sola tabla)
--
-- 3. Compatibilidad:
--    - No afecta consultas existentes (campo NULL por defecto)
--    - No rompe lógica actual de marcaciones
--    - No modifica cálculos financieros
--    - Puede integrarse gradualmente en dashboard y reportes
--
-- ============================================
