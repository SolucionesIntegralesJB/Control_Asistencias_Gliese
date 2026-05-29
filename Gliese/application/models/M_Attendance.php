<?php
// --
class M_Attendance extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // -- Get attendance list with filters
    public function get_attendance_list($bind = array())
    {
        // --
        try {
            // -- Build base query (simplified to avoid potential table issues)
            $sql = 'SELECT
                    s.id,
                    s.employee_id,
                    e.name AS employee_name,
                    e.position,
                    s.shift_date,
                    s.scheduled_start,
                    s.scheduled_end,
                    s.actual_start,
                    s.actual_end,
                    s.break_start,
                    s.break_end,
                    s.break_duration,
                    s.regular_hours,
                    s.overtime_hours,
                    s.hourly_rate,
                    s.overtime_rate,
                    s.regular_payment,
                    s.overtime_payment,
                    s.total_payment,
                    s.status
                FROM attendance_shifts s
                LEFT JOIN employees e ON s.employee_id = e.id
                WHERE 1=1';

            // -- Apply filters
            if (!empty($bind['employee_id'])) {
                $sql .= ' AND s.employee_id = :employee_id';
            }
            if (!empty($bind['start_date'])) {
                $sql .= ' AND s.shift_date >= :start_date';
            }
            if (!empty($bind['end_date'])) {
                $sql .= ' AND s.shift_date <= :end_date';
            }
            if (!empty($bind['status'])) {
                $sql .= ' AND s.status = :status';
            }
            // -- Temporarily disable campus filter
            // if (!empty($bind['campus_id'])) {
            //     $sql .= ' AND s.campus_id = :campus_id';
            // }

            // -- Order and limit
            $sql .= ' ORDER BY s.shift_date DESC, s.actual_start DESC';

            if (!empty($bind['limit'])) {
                $sql .= ' LIMIT :limit';
                if (!empty($bind['offset'])) {
                    $sql .= ' OFFSET :offset';
                }
            }

            // --
            $result = $this->pdo->fetchAll($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Get attendance count for pagination
    public function get_attendance_count($bind = array())
    {
        // --
        try {
            // -- Build base query
            $sql = 'SELECT COUNT(*) as total
                FROM attendance_shifts s
                LEFT JOIN employees e ON s.employee_id = e.id
                WHERE 1=1';
            
            // -- Apply filters
            if (!empty($bind['employee_id'])) {
                $sql .= ' AND s.employee_id = :employee_id';
            }
            if (!empty($bind['start_date'])) {
                $sql .= ' AND s.shift_date >= :start_date';
            }
            if (!empty($bind['end_date'])) {
                $sql .= ' AND s.shift_date <= :end_date';
            }
            if (!empty($bind['status'])) {
                $sql .= ' AND s.status = :status';
            }
            if (!empty($bind['campus_id'])) {
                $sql .= ' AND s.campus_id = :campus_id';
            }
            
            // --
            $result = $this->pdo->fetchOne($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array('total' => 0));
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Get attendance detail by ID
    public function get_attendance_detail($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    s.*,
                    e.name AS employee_name,
                    e.email AS employee_email,
                    e.position,
                    c.description AS campus_name,
                    jr.job_role
                FROM attendance_shifts s
                LEFT JOIN employees e ON s.employee_id = e.id
                LEFT JOIN campus c ON s.campus_id = c.id
                LEFT JOIN job_role jr ON s.job_role_id = jr.id
                WHERE s.id = :id';
            // --
            $result = $this->pdo->fetchOne($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Get shift events for timeline
    public function get_shift_events($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id,
                    shift_id,
                    event_type,
                    event_time,
                    event_data,
                    created_by,
                    created_at
                FROM attendance_shift_events
                WHERE shift_id = :shift_id
                ORDER BY event_time ASC';
            // --
            $result = $this->pdo->fetchAll($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Calculate and update payment for a shift
    public function calculate_payment($bind)
    {
        // --
        try {
            // -- Get shift data
            $sql = 'SELECT 
                    id,
                    regular_hours,
                    overtime_hours,
                    hourly_rate,
                    overtime_rate
                FROM attendance_shifts
                WHERE id = :id';
            // --
            $shift = $this->pdo->fetchOne($sql, array('id' => $bind['id']));
            
            if (!$shift) {
                return array('status' => 'ERROR', 'result' => array('msg' => 'Shift not found'));
            }
            
            // -- Calculate payments
            $regular_payment = $shift['regular_hours'] * $shift['hourly_rate'];
            $overtime_payment = $shift['overtime_hours'] * $shift['overtime_rate'];
            $total_payment = $regular_payment + $overtime_payment;
            
            // -- Update shift with calculated payments
            $sql = 'UPDATE attendance_shifts
                SET
                    regular_payment = :regular_payment,
                    overtime_payment = :overtime_payment,
                    total_payment = :total_payment,
                    payment_calculated_at = NOW()
                WHERE id = :id';
            
            $update_bind = array(
                'id' => $bind['id'],
                'regular_payment' => $regular_payment,
                'overtime_payment' => $overtime_payment,
                'total_payment' => $total_payment
            );
            
            $result = $this->pdo->perform($sql, $update_bind);
            
            if ($result) {
                // -- Create event for rate change
                $this->create_shift_event(array(
                    'shift_id' => $bind['id'],
                    'event_type' => 'rate_change',
                    'event_data' => json_encode(array(
                        'regular_payment' => $regular_payment,
                        'overtime_payment' => $overtime_payment,
                        'total_payment' => $total_payment
                    )),
                    'created_by' => isset($bind['created_by']) ? $bind['created_by'] : null
                ));
                
                $response = array('status' => 'OK', 'result' => array(
                    'regular_payment' => $regular_payment,
                    'overtime_payment' => $overtime_payment,
                    'total_payment' => $total_payment
                ));
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Recalculate shift data (hours and payments)
    public function recalculate_shift_data($shift_id)
    {
        // --
        try {
            // -- Get shift data
            $sql = 'SELECT * FROM attendance_shifts WHERE id = :id';
            $shift = $this->pdo->fetchOne($sql, array('id' => $shift_id));

            if (!$shift) {
                return array('status' => 'ERROR', 'result' => array('msg' => 'Shift not found'));
            }

            // -- Calculate total worked minutes
            $total_worked_minutes = 0;
            $break_duration = 0;

            if ($shift['actual_start'] && $shift['actual_end']) {
                // -- Parse times
                $start = strtotime($shift['actual_start']);
                $end = strtotime($shift['actual_end']);

                // -- Handle overnight shifts
                if ($end < $start) {
                    $end += 86400; // Add 24 hours
                }

                $total_worked_minutes = ($end - $start) / 60;
            }

            // -- Calculate break duration
            if ($shift['break_start'] && $shift['break_end']) {
                $break_start = strtotime($shift['break_start']);
                $break_end = strtotime($shift['break_end']);

                if ($break_end < $break_start) {
                    $break_end += 86400;
                }

                $break_duration = ($break_end - $break_start) / 60;
            }

            // -- Update break duration
            $break_duration = max(0, $break_duration);

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

            // -- Calculate payments (handle NULL rates independently)
            $regular_payment = 0;
            $overtime_payment = 0;
            $total_payment = 0;

            // -- Calculate regular payment if hourly_rate exists
            if ($shift['hourly_rate'] !== null && $shift['hourly_rate'] > 0) {
                $regular_payment = $regular_hours * $shift['hourly_rate'];
            }

            // -- Calculate overtime payment if overtime_rate exists
            if ($shift['overtime_rate'] !== null && $shift['overtime_rate'] > 0) {
                $overtime_payment = $overtime_hours * $shift['overtime_rate'];
            }

            // -- Calculate total payment
            $total_payment = $regular_payment + $overtime_payment;

            // -- Validate and round to DECIMAL(10,2)
            $regular_payment = round(max(0, floatval($regular_payment)), 2);
            $overtime_payment = round(max(0, floatval($overtime_payment)), 2);
            $total_payment = round(max(0, floatval($total_payment)), 2);

            // -- Update shift with calculated values
            $sql = 'UPDATE attendance_shifts
                SET
                    break_duration = :break_duration,
                    total_worked_minutes = :total_worked_minutes,
                    regular_hours = :regular_hours,
                    overtime_hours = :overtime_hours,
                    regular_payment = :regular_payment,
                    overtime_payment = :overtime_payment,
                    total_payment = :total_payment,
                    payment_calculated_at = NOW()
                WHERE id = :id';

            $update_bind = array(
                'id' => $shift_id,
                'break_duration' => $break_duration,
                'total_worked_minutes' => $total_worked_minutes,
                'regular_hours' => $regular_hours,
                'overtime_hours' => $overtime_hours,
                'regular_payment' => $regular_payment,
                'overtime_payment' => $overtime_payment,
                'total_payment' => $total_payment
            );

            $result = $this->pdo->perform($sql, $update_bind);

            if ($result) {
                // -- Create event for recalculation
                $this->create_shift_event(array(
                    'shift_id' => $shift_id,
                    'event_type' => 'rate_change',
                    'event_data' => json_encode(array(
                        'break_duration' => $break_duration,
                        'total_worked_minutes' => $total_worked_minutes,
                        'regular_hours' => $regular_hours,
                        'overtime_hours' => $overtime_hours,
                        'regular_payment' => $regular_payment,
                        'overtime_payment' => $overtime_payment,
                        'total_payment' => $total_payment
                    )),
                    'created_by' => null
                ));

                $response = array('status' => 'OK', 'result' => array(
                    'break_duration' => $break_duration,
                    'total_worked_minutes' => $total_worked_minutes,
                    'regular_hours' => $regular_hours,
                    'overtime_hours' => $overtime_hours,
                    'regular_payment' => $regular_payment,
                    'overtime_payment' => $overtime_payment,
                    'total_payment' => $total_payment
                ));
            } else {
                $response = array('status' => 'ERROR', 'result' => array('msg' => 'Failed to update shift'));
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Update shift times (manual edit)
    public function update_shift($bind)
    {
        // --
        try {
            // -- Get current shift data for event logging
            $sql = 'SELECT * FROM attendance_shifts WHERE id = :id';
            $current_shift = $this->pdo->fetchOne($sql, array('id' => $bind['id']));

            if (!$current_shift) {
                return array('status' => 'ERROR', 'result' => array('msg' => 'Shift not found'));
            }

            // -- Build update query dynamically based on provided fields
            $update_fields = array();
            $update_bind = array('id' => $bind['id']);

            if (isset($bind['actual_start'])) {
                $update_fields[] = 'actual_start = :actual_start';
                $update_bind['actual_start'] = $bind['actual_start'];
            }
            if (isset($bind['actual_end'])) {
                $update_fields[] = 'actual_end = :actual_end';
                $update_bind['actual_end'] = $bind['actual_end'];
            }
            if (isset($bind['break_start'])) {
                $update_fields[] = 'break_start = :break_start';
                $update_bind['break_start'] = $bind['break_start'];
            }
            if (isset($bind['break_end'])) {
                $update_fields[] = 'break_end = :break_end';
                $update_bind['break_end'] = $bind['break_end'];
            }
            if (isset($bind['hourly_rate'])) {
                $update_fields[] = 'hourly_rate = :hourly_rate';
                $update_bind['hourly_rate'] = $bind['hourly_rate'];
            }
            if (isset($bind['overtime_rate'])) {
                $update_fields[] = 'overtime_rate = :overtime_rate';
                $update_bind['overtime_rate'] = $bind['overtime_rate'];
            }

            if (empty($update_fields)) {
                return array('status' => 'ERROR', 'result' => array('msg' => 'No fields to update'));
            }

            $sql = 'UPDATE attendance_shifts
                SET ' . implode(', ', $update_fields) . '
                WHERE id = :id';

            $result = $this->pdo->perform($sql, $update_bind);

            if ($result) {
                // -- Create event for manual edit
                $this->create_shift_event(array(
                    'shift_id' => $bind['id'],
                    'event_type' => 'manual_edit',
                    'event_data' => json_encode(array(
                        'previous' => $current_shift,
                        'changes' => $bind
                    )),
                    'created_by' => isset($bind['created_by']) ? $bind['created_by'] : null
                ));

                // -- Recalculate shift data (hours and payments) if times were changed
                $time_fields_changed = isset($bind['actual_start']) || isset($bind['actual_end']) ||
                                       isset($bind['break_start']) || isset($bind['break_end']);
                $rate_fields_changed = isset($bind['hourly_rate']) || isset($bind['overtime_rate']);

                if ($time_fields_changed || $rate_fields_changed) {
                    $this->recalculate_shift_data($bind['id']);
                }

                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Create shift event
    public function create_shift_event($bind)
    {
        // --
        try {
            // --
            $sql = 'INSERT INTO attendance_shift_events
                (shift_id, event_type, event_data, created_by)
                VALUES
                (:shift_id, :event_type, :event_data, :created_by)';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => array());
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Get employees list for filter
    public function get_employees_list()
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id,
                    name,
                    position
                FROM employees
                WHERE status = 1
                ORDER BY name ASC';
            // --
            $result = $this->pdo->fetchAll($sql);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // -- Get campus list for filter
    public function get_campus_list()
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id,
                    description
                FROM campus
                WHERE status = 1
                ORDER BY description ASC';
            // --
            $result = $this->pdo->fetchAll($sql);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }
}
