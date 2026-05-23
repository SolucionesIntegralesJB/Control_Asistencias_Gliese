# Estructura de Base de Datos - Attendance System

## Tablas Creadas

### 1. attendance_shifts (Turnos de trabajo)
**Propósito:** Almacena información de cada turno de trabajo diario por usuario.

**Campos principales:**
- `user_id` - FK a tabla `user` del dashboard Gliese (INT)
- `job_role_id` - FK a tabla `job_role` (rol/trabajo seleccionado) (INT)
- `campus_id` - FK a tabla `campus` (ubicación de trabajo) (INT)
- `shift_date` - Fecha del turno (YYYY-MM-DD)
- `scheduled_start/end` - Horarios programados (8 horas base)
- `actual_start/end` - Horarios reales (check-in/check-out)
- `break_start/end` - Horarios del break (único por turno)
- `break_duration` - Duración del break en minutos (calculado automáticamente)
- `total_worked_minutes` - Total minutos trabajados
- `regular_hours` - Horas regulares (máximo 8)
- `overtime_hours` - Horas extra (excedente de 8)
- `status` - Estado: pending, in_progress, completed, cancelled

**Restricciones:**
- `UNIQUE KEY (user_id, shift_date)` - Garantiza 1 turno por usuario por día
- FKs a tablas existentes del dashboard

### 2. attendance_records (Registro de marcaciones)
**Propósito:** Almacena cada marcación manual realizada por botones.

**Campos principales:**
- `shift_id` - FK a `attendance_shifts`
- `record_type` - Tipo: check_in, check_out, break_start, break_end
- `record_time` - Timestamp exacto de la marcación
- `location` - Ubicación (opcional)
- `ip_address` - IP del usuario (auditoría)
- `user_agent` - Navegador/dispositivo (auditoría)

**Relación:**
- FK a `attendance_shifts` con CASCADE DELETE

### 3. attendance_settings (Configuración del sistema)
**Propósito:** Almacena configuraciones globales del sistema.

**Configuraciones por defecto:**
- `regular_hours_limit` = 8 (límite horas regulares)
- `break_auto_duration` = 30 (duración break en minutos)
- `break_required` = 1 (break obligatorio)
- `overtime_calculation` = 1 (calcular horas extra)
- `grace_period_minutes` = 15 (período gracia check-in tardío)

## Relaciones Entre Tablas

```
user (Gliese)
  ↓ (FK)
attendance_shifts
  ↓ (FK)
attendance_records

job_role (Gliese)
  ↓ (FK)
attendance_shifts

campus (Gliese)
  ↓ (FK)
attendance_shifts
```

## Por Qué Esta Estructura

### 1. **Simplicidad**
- Solo 3 tablas nuevas (shifts, records, settings)
- Reutiliza tablas existentes del dashboard (user, job_role, campus)
- No crea tablas redundantes

### 2. **Un turno por día**
- Restricción UNIQUE en `(user_id, shift_date)` garantiza esto a nivel BD
- Evita duplicados y conflictos

### 3. **Break único por turno**
- Campos `break_start`, `break_end`, `break_duration` en la misma tabla de turnos
- No requiere tabla separada para breaks
- Simplifica cálculos y consultas

### 4. **Cálculo automático de horas**
- `regular_hours` = min(8, total_worked_hours)
- `overtime_hours` = max(0, total_worked_hours - 8)
- `break_duration` = calculado automáticamente al registrar break_end

### 5. **Auditoría completa**
- `attendance_records` guarda cada marcación con timestamp
- IP y user_agent para trazabilidad
- Historial completo de acciones

### 6. **Flexibilidad**
- `status` enum permite múltiples estados del turno
- `notes` para información adicional
- Configuración externa en `attendance_settings`

### 7. **Optimización**
- Índices en campos frecuentemente consultados
- Índices compuestos para consultas comunes
- Vistas predefinidas para reportes rápidos

## Flujo de Datos

### 1. Inicio de Turno
1. Usuario selecciona `job_role` y `campus`
2. Sistema crea registro en `attendance_shifts` (status: pending)
3. Usuario hace check-in → registro en `attendance_records` (check_in)
4. Sistema actualiza `actual_start` y cambia status a `in_progress`

### 2. Durante Turno
1. Usuario inicia break → registro en `attendance_records` (break_start)
2. Sistema actualiza `break_start` en `attendance_shifts`
3. Usuario termina break → registro en `attendance_records` (break_end)
4. Sistema calcula `break_duration` y actualiza `break_end`

### 3. Fin de Turno
1. Usuario hace check-out → registro en `attendance_records` (check_out)
2. Sistema actualiza `actual_end` en `attendance_shifts`
3. Sistema calcula:
   - `total_worked_minutes` = (actual_end - actual_start) - break_duration
   - `regular_hours` = min(8, total_worked_minutes / 60)
   - `overtime_hours` = max(0, total_worked_minutes / 60 - 8)
4. Cambia status a `completed`

## Ventajas de Esta Estructura

### Mantenimiento
- Fácil de entender y modificar
- Dependencias claras entre tablas
- Documentación inline en comentarios

### Escalabilidad
- Puede agregar más configuraciones en `attendance_settings`
- Puede agregar más tipos de marcaciones en `record_type` enum
- Puede extender con nuevas tablas sin romper estructura existente

### Performance
- Índices optimizados para consultas frecuentes
- Vistas precompiladas para reportes
- FKs con CASCADE DELETE para integridad referencial

### Compatibilidad
- Reutiliza tablas existentes del dashboard Gliese
- No modifica estructura de tablas del dashboard
- Puede coexistir con otros sistemas en la misma BD

## Consultas Frecuentes

### Obtener turnos de un usuario en un rango de fechas
```sql
SELECT * FROM attendance_shifts 
WHERE user_id = ? 
AND shift_date BETWEEN ? AND ?
ORDER BY shift_date DESC;
```

### Obtener marcaciones de un turno
```sql
SELECT * FROM attendance_records 
WHERE shift_id = ? 
ORDER BY record_time ASC;
```

### Calcular horas trabajadas por usuario en un período
```sql
SELECT 
    user_id,
    SUM(regular_hours) as total_regular,
    SUM(overtime_hours) as total_overtime
FROM attendance_shifts
WHERE shift_date BETWEEN ? AND ?
AND status = 'completed'
GROUP BY user_id;
```

### Verificar si usuario tiene turno activo
```sql
SELECT * FROM attendance_shifts 
WHERE user_id = ? 
AND shift_date = CURDATE()
AND status = 'in_progress';
```
