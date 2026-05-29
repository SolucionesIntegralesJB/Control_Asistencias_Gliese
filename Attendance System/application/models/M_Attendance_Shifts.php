<?php
// Attendance Shifts Model - Attendance System
// DEBUG MODE ACTIVADO TEMPORALMENTE
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/attendance_debug.log');

class M_Attendance_Shifts extends Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function create_shift($bind) {
        error_log("DEBUG M_Attendance_Shifts::create_shift() - Iniciado");
        error_log("DEBUG M_Attendance_Shifts::create_shift() - Bind: " . print_r($bind, true));

        try {
            $sql = 'INSERT INTO attendance_shifts
                    (employee_id, job_role_id, campus_id, shift_date, scheduled_start, scheduled_end, work_description, status)
                    VALUES
                    (:employee_id, :job_role_id, :campus_id, :shift_date, :scheduled_start, :scheduled_end, :work_description, :status)';

            error_log("DEBUG M_Attendance_Shifts::create_shift() - SQL: " . $sql);

            // ERROR CORREGIDO: PDO no tiene método perform(), usar prepare() + execute()
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);

            error_log("DEBUG M_Attendance_Shifts::create_shift() - Execute result: " . ($result ? 'true' : 'false'));

            if ($result) {
                $shift_id = $this->pdo->lastInsertId();
                error_log("DEBUG M_Attendance_Shifts::create_shift() - Shift ID: $shift_id");
                $response = array('status' => 'OK', 'result' => array('shift_id' => $shift_id));
            } else {
                error_log("DEBUG M_Attendance_Shifts::create_shift() - Error en execute");
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            error_log("DEBUG M_Attendance_Shifts::create_shift() - PDO Exception: " . $e->getMessage());
            error_log("DEBUG M_Attendance_Shifts::create_shift() - PDO Exception Code: " . $e->getCode());
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        error_log("DEBUG M_Attendance_Shifts::create_shift() - Response: " . print_r($response, true));
        return $response;
    }

    public function get_shift_by_employee_date($bind) {
        try {
            $sql = 'SELECT 
                    id, employee_id, job_role_id, campus_id, shift_date,
                    scheduled_start, scheduled_end,
                    CONCAT(shift_date, " ", actual_start) as actual_start,
                    CONCAT(shift_date, " ", actual_end) as actual_end,
                    CONCAT(shift_date, " ", break_start) as break_start,
                    CONCAT(shift_date, " ", break_end) as break_end,
                    break_duration, total_worked_minutes, regular_hours, overtime_hours,
                    status, notes, created_at, updated_at
                    FROM attendance_shifts
                    WHERE employee_id = :employee_id AND shift_date = :shift_date';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    public function update_shift_start($bind) {
        try {
            $sql = 'UPDATE attendance_shifts 
                    SET actual_start = :actual_start, status = :status
                    WHERE id = :shift_id';
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function update_break_start($bind) {
        try {
            $sql = 'UPDATE attendance_shifts 
                    SET break_start = :break_start
                    WHERE id = :shift_id';
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function update_break_end($bind) {
        try {
            $sql = 'UPDATE attendance_shifts 
                    SET break_end = :break_end, break_duration = :break_duration
                    WHERE id = :shift_id';
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function update_shift_end($bind) {
        try {
            $sql = 'UPDATE attendance_shifts
                    SET actual_end = :actual_end,
                        total_worked_minutes = :total_worked_minutes,
                        regular_hours = :regular_hours,
                        overtime_hours = :overtime_hours,
                        status = :status
                    WHERE id = :shift_id';

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    // -- Recalculate shift data (hours) - centralized method
    public function recalculate_shift_data($shift_id) {
        try {
            // -- Get shift data
            $sql = 'SELECT * FROM attendance_shifts WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array('id' => $shift_id));
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$shift) {
                return array('status' => 'ERROR', 'result' => array('msg' => 'Shift not found'));
            }

            // -- Calculate total worked minutes
            $total_worked_minutes = 0;
            $break_duration = isset($shift['break_duration']) ? $shift['break_duration'] : 0;

            if ($shift['actual_start'] && $shift['actual_end']) {
                // -- Parse times (actual_start is DATETIME, actual_end is TIME)
                $start = strtotime($shift['actual_start']);
                $shift_date = date('Y-m-d', strtotime($shift['actual_start']));
                $end = strtotime($shift_date . ' ' . $shift['actual_end']);

                // -- Handle overnight shifts
                if ($end < $start) {
                    $end += 86400; // Add 24 hours
                }

                $total_worked_minutes = round(($end - $start) / 60) - $break_duration;
            }

            // -- Update break duration
            $break_duration = max(0, $break_duration);
            $total_worked_minutes = max(0, $total_worked_minutes);

            // -- Calculate regular and overtime hours (8 hours = 480 minutes)
            $regular_hours = 0;
            $overtime_hours = 0;

            if ($total_worked_minutes > 0) {
                if ($total_worked_minutes <= 480) {
                    $regular_hours = $total_worked_minutes / 60;
                } else {
                    $regular_hours = 8;
                    $overtime_hours = ($total_worked_minutes - 480) / 60;
                }
            }

            // -- Update shift with calculated values
            $sql = 'UPDATE attendance_shifts
                SET
                    break_duration = :break_duration,
                    total_worked_minutes = :total_worked_minutes,
                    regular_hours = :regular_hours,
                    overtime_hours = :overtime_hours
                WHERE id = :id';

            $update_bind = array(
                'id' => $shift_id,
                'break_duration' => $break_duration,
                'total_worked_minutes' => $total_worked_minutes,
                'regular_hours' => number_format($regular_hours, 2),
                'overtime_hours' => number_format($overtime_hours, 2)
            );

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($update_bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array(
                    'break_duration' => $break_duration,
                    'total_worked_minutes' => $total_worked_minutes,
                    'regular_hours' => $regular_hours,
                    'overtime_hours' => $overtime_hours
                ));
            } else {
                $response = array('status' => 'ERROR', 'result' => array('msg' => 'Failed to update shift'));
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }

        return $response;
    }

    public function get_shift_by_id($bind) {
        try {
            $sql = 'SELECT * FROM attendance_shifts WHERE id = :shift_id';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function get_employee_shifts($bind) {
        try {
            $sql = 'SELECT 
                    s.id, s.employee_id, s.job_role_id, s.campus_id, s.shift_date,
                    s.scheduled_start, s.scheduled_end,
                    CONCAT(s.shift_date, " ", s.actual_start) as actual_start,
                    CONCAT(s.shift_date, " ", s.actual_end) as actual_end,
                    CONCAT(s.shift_date, " ", s.break_start) as break_start,
                    CONCAT(s.shift_date, " ", s.break_end) as break_end,
                    s.break_duration, s.total_worked_minutes, s.regular_hours, s.overtime_hours,
                    s.status, s.notes, s.created_at, s.updated_at,
                    jr.job_role, c.description as campus_name
                    FROM attendance_shifts s
                    LEFT JOIN job_role jr ON s.job_role_id = jr.id
                    LEFT JOIN campus c ON s.campus_id = c.id
                    WHERE s.employee_id = :employee_id
                    ORDER BY s.shift_date DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    public function get_current_active_shift($bind) {
        try {
            $sql = 'SELECT 
                    id, employee_id, job_role_id, campus_id, shift_date,
                    scheduled_start, scheduled_end,
                    CONCAT(shift_date, " ", actual_start) as actual_start,
                    CONCAT(shift_date, " ", actual_end) as actual_end,
                    CONCAT(shift_date, " ", break_start) as break_start,
                    CONCAT(shift_date, " ", break_end) as break_end,
                    break_duration, total_worked_minutes, regular_hours, overtime_hours,
                    status, notes, created_at, updated_at
                    FROM attendance_shifts 
                    WHERE employee_id = :employee_id 
                    AND shift_date = CURDATE()';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function get_employee_statistics($bind) {
        try {
            $employee_id = $bind['employee_id'];

            // Estadísticas del día
            $sql_day = 'SELECT
                        COALESCE(SUM(regular_hours), 0) as day_hours,
                        COALESCE(SUM(overtime_hours), 0) as day_overtime,
                        COALESCE(SUM(break_duration), 0) as day_break_minutes
                        FROM attendance_shifts
                        WHERE employee_id = :employee_id
                        AND shift_date = CURDATE()
                        AND status = "completed"';

            $stmt = $this->pdo->prepare($sql_day);
            $stmt->execute(array('employee_id' => $employee_id));
            $day_stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Estadísticas de la semana
            $sql_week = 'SELECT
                         COALESCE(SUM(regular_hours), 0) as week_hours,
                         COALESCE(SUM(overtime_hours), 0) as week_overtime,
                         COALESCE(SUM(break_duration), 0) as week_break_minutes
                         FROM attendance_shifts
                         WHERE employee_id = :employee_id
                         AND shift_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                         AND status = "completed"';

            $stmt = $this->pdo->prepare($sql_week);
            $stmt->execute(array('employee_id' => $employee_id));
            $week_stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Estadísticas del mes
            $sql_month = 'SELECT
                          COALESCE(SUM(regular_hours), 0) as month_hours,
                          COALESCE(SUM(overtime_hours), 0) as month_overtime,
                          COALESCE(SUM(break_duration), 0) as month_break_minutes
                          FROM attendance_shifts
                          WHERE employee_id = :employee_id
                          AND shift_date >= DATE_FORMAT(CURDATE(), \'%Y-%m-01\')
                          AND status = "completed"';

            $stmt = $this->pdo->prepare($sql_month);
            $stmt->execute(array('employee_id' => $employee_id));
            $month_stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = array(
                'status' => 'OK',
                'result' => array(
                    'day' => $day_stats,
                    'week' => $week_stats,
                    'month' => $month_stats
                )
            );
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }
}
