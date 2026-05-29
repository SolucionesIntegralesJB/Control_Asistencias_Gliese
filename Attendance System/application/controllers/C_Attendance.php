<?php
// Attendance Controller - Attendance System
// DEBUG MODE ACTIVADO TEMPORALMENTE
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/attendance_debug.log');

class C_Attendance extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->functions->validate_session($this->session->get('is_logged'));

        $data = array(
            'employee_name' => $this->session->get('employee_name'),
            'employee_email' => $this->session->get('employee_email'),
            'employee_position' => $this->session->get('employee_position'),
            'employee_work_area' => $this->session->get('employee_work_area')
        );

        $this->view->set_data($data);
        $this->view->set_view('index');
    }

    public function start_shift() {
        header('Content-Type: application/json');

        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }

            if (isset($input['job_role_id']) && isset($input['campus_id'])) {
                try {
                    // -- Validar work_description
                    if (!isset($input['work_description']) || empty(trim($input['work_description']))) {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'La descripción del trabajo es obligatoria'
                        );
                        echo json_encode($json);
                        return;
                    }

                    $work_description = trim($input['work_description']);
                    if (strlen($work_description) < 5) {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'La descripción del trabajo debe tener al menos 5 caracteres'
                        );
                        echo json_encode($json);
                        return;
                    }

                    if (strlen($work_description) > 500) {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'La descripción del trabajo no puede exceder 500 caracteres'
                        );
                        echo json_encode($json);
                        return;
                    }

                    $obj_shifts = $this->load_model('Attendance_Shifts');
                    $obj_records = $this->load_model('Attendance_Records');

                    $employee_id = $this->session->get('employee_id');
                    $shift_date = date('Y-m-d');
                    $current_time = date('H:i:s');

                    // Validar: un solo turno por día
                    $bind_check = array(
                        'employee_id' => $employee_id,
                        'shift_date' => $shift_date
                    );
                    $existing_shift = $obj_shifts->get_shift_by_employee_date($bind_check);

                    if ($existing_shift['status'] === 'OK') {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'Ya existe un turno para hoy'
                        );
                    } else {
                        // Crear turno
                        $scheduled_start = $current_time;
                        $scheduled_end = date('H:i:s', strtotime('+8 hours', strtotime($current_time)));

                        $bind_shift = array(
                            'employee_id' => $employee_id,
                            'job_role_id' => $this->functions->clean_string($input['job_role_id']),
                            'campus_id' => $this->functions->clean_string($input['campus_id']),
                            'shift_date' => $shift_date,
                            'scheduled_start' => $scheduled_start,
                            'scheduled_end' => $scheduled_end,
                            'work_description' => $this->functions->clean_string($work_description),
                            'status' => 'pending'
                        );

                        $response_shift = $obj_shifts->create_shift($bind_shift);

                        if ($response_shift['status'] === 'OK') {
                            $shift_id = $response_shift['result']['shift_id'];

                            // Actualizar hora de inicio
                            $bind_update = array(
                                'shift_id' => $shift_id,
                                'actual_start' => $current_time,
                                'status' => 'in_progress'
                            );
                            $obj_shifts->update_shift_start($bind_update);

                            // Registrar marcación
                            $bind_record = array(
                                'shift_id' => $shift_id,
                                'record_type' => 'check_in',
                                'record_time' => date('Y-m-d H:i:s'),
                                'location' => isset($input['location']) ? $this->functions->clean_string($input['location']) : null,
                                'ip_address' => $_SERVER['REMOTE_ADDR'],
                                'user_agent' => $_SERVER['HTTP_USER_AGENT']
                            );
                            $obj_records->create_record($bind_record);

                            $json = array(
                                'status' => 'OK',
                                'msg' => 'Turno iniciado correctamente',
                                'shift_id' => $shift_id
                            );
                        } else {
                            $error_msg = 'Error al crear turno';
                            if ($response_shift['status'] === 'EXCEPTION') {
                                $error_msg .= ': ' . $response_shift['result'];
                            }
                            $json = array(
                                'status' => 'ERROR',
                                'msg' => $error_msg
                            );
                        }
                    }
                } catch (Exception $e) {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'Excepción: ' . $e->getMessage()
                    );
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Verificar parámetros'
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        echo json_encode($json);
    }

    public function start_break() {
        header('Content-Type: application/json');

        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $obj_shifts = $this->load_model('Attendance_Shifts');
                $obj_records = $this->load_model('Attendance_Records');

                $employee_id = $this->session->get('employee_id');
                $shift_date = date('Y-m-d');
                $current_time = date('H:i:s');

                // Obtener turno actual
                $bind_shift = array(
                    'employee_id' => $employee_id,
                    'shift_date' => $shift_date
                );
                $shift = $obj_shifts->get_shift_by_employee_date($bind_shift);

                if ($shift['status'] === 'ERROR') {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'No existe turno activo'
                    );
                } elseif ($shift['result']['status'] !== 'in_progress') {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'El turno no está en progreso'
                    );
                } else {
                    $shift_id = $shift['result']['id'];

                    // Validar: un solo break por turno
                    $bind_check = array(
                        'shift_id' => $shift_id,
                        'record_type' => 'break_start'
                    );
                    $break_started = $obj_records->has_break_started($bind_check);

                    if ($break_started['result']) {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'Ya existe un break iniciado'
                        );
                    } else {
                        // Iniciar break
                        $bind_update = array(
                            'shift_id' => $shift_id,
                            'break_start' => $current_time
                        );
                        $obj_shifts->update_break_start($bind_update);

                        // Registrar marcación
                        $bind_record = array(
                            'shift_id' => $shift_id,
                            'record_type' => 'break_start',
                            'record_time' => date('Y-m-d H:i:s'),
                            'location' => null,
                            'ip_address' => $_SERVER['REMOTE_ADDR'],
                            'user_agent' => $_SERVER['HTTP_USER_AGENT']
                        );
                        $obj_records->create_record($bind_record);

                        $json = array(
                            'status' => 'OK',
                            'msg' => 'Break iniciado correctamente'
                        );
                    }
                }
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Excepción: ' . $e->getMessage()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        echo json_encode($json);
    }

    public function end_break() {
        header('Content-Type: application/json');

        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $obj_shifts = $this->load_model('Attendance_Shifts');
                $obj_records = $this->load_model('Attendance_Records');
                $obj_settings = $this->load_model('Attendance_Settings');

                $employee_id = $this->session->get('employee_id');
                $shift_date = date('Y-m-d');
                $current_time = date('H:i:s');

                // Obtener turno actual
                $bind_shift = array(
                    'employee_id' => $employee_id,
                    'shift_date' => $shift_date
                );
                $shift = $obj_shifts->get_shift_by_employee_date($bind_shift);

                if ($shift['status'] === 'ERROR') {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'No existe turno activo'
                    );
                } else {
                    $shift_id = $shift['result']['id'];
                    $shift_data = $shift['result'];

                    // Validar: no finalizar break si no fue iniciado
                    $bind_check = array(
                        'shift_id' => $shift_id,
                        'record_type' => 'break_start'
                    );
                    $break_started = $obj_records->has_break_started($bind_check);

                    if (!$break_started['result']) {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'No se ha iniciado el break'
                        );
                    } else {
                        // Validar: break ya finalizado
                        $bind_check_end = array(
                            'shift_id' => $shift_id,
                            'record_type' => 'break_end'
                        );
                        $break_ended = $obj_records->has_break_ended($bind_check_end);

                        if ($break_ended['result']) {
                            $json = array(
                                'status' => 'ERROR',
                                'msg' => 'El break ya fue finalizado'
                            );
                        } else {
                            // Calcular duración del break
                            $break_start = $shift_data['break_start'];
                            error_log("DEBUG end_break: break_start = " . $break_start);
                            error_log("DEBUG end_break: shift_date = " . $shift_date);
                            error_log("DEBUG end_break: current_time = " . $current_time);
                            
                            // break_start ahora es DATETIME completo (YYYY-MM-DD HH:MM:SS)
                            // No concatenar con shift_date
                            $break_start_timestamp = strtotime($break_start);
                            $break_end_timestamp = strtotime($shift_date . ' ' . $current_time);
                            
                            error_log("DEBUG end_break: break_start_timestamp = " . $break_start_timestamp);
                            error_log("DEBUG end_break: break_end_timestamp = " . $break_end_timestamp);
                            
                            if ($break_start_timestamp === false || $break_end_timestamp === false) {
                                error_log("DEBUG end_break: Error al convertir fechas a timestamp");
                                $json = array(
                                    'status' => 'ERROR',
                                    'msg' => 'Error al calcular duración del break'
                                );
                            } else {
                                $break_duration_minutes = round(($break_end_timestamp - $break_start_timestamp) / 60);
                                error_log("DEBUG end_break: break_duration_minutes = " . $break_duration_minutes);
                                
                                // Validar duración negativa
                                if ($break_duration_minutes < 0) {
                                    error_log("DEBUG end_break: Duración negativa, usando 0");
                                    $break_duration_minutes = 0;
                                }

                                // Finalizar break
                                $bind_update = array(
                                    'shift_id' => $shift_id,
                                    'break_end' => $current_time,
                                    'break_duration' => $break_duration_minutes
                                );
                                $obj_shifts->update_break_end($bind_update);

                                // Recalcular datos del turno para asegurar consistencia
                                $obj_shifts->recalculate_shift_data($shift_id);

                                // Registrar marcación
                                $bind_record = array(
                                    'shift_id' => $shift_id,
                                    'record_type' => 'break_end',
                                    'record_time' => date('Y-m-d H:i:s'),
                                    'location' => null,
                                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                                );
                                $obj_records->create_record($bind_record);

                                $json = array(
                                    'status' => 'OK',
                                    'msg' => 'Break finalizado correctamente',
                                    'break_duration' => $break_duration_minutes
                                );
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Excepción: ' . $e->getMessage()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        echo json_encode($json);
    }

    public function end_shift() {
        header('Content-Type: application/json');

        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $obj_shifts = $this->load_model('Attendance_Shifts');
                $obj_records = $this->load_model('Attendance_Records');
                $obj_settings = $this->load_model('Attendance_Settings');

                $employee_id = $this->session->get('employee_id');
                $shift_date = date('Y-m-d');
                $current_time = date('H:i:s');

                // Obtener turno actual
                $bind_shift = array(
                    'employee_id' => $employee_id,
                    'shift_date' => $shift_date
                );
                $shift = $obj_shifts->get_shift_by_employee_date($bind_shift);

                if ($shift['status'] === 'ERROR') {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'No existe turno activo'
                    );
                } elseif ($shift['result']['status'] !== 'in_progress') {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'El turno no está en progreso'
                    );
                } else {
                    $shift_id = $shift['result']['id'];
                    $shift_data = $shift['result'];

                    // Validar: no finalizar turno sin iniciarlo
                    if (!$shift_data['actual_start']) {
                        $json = array(
                            'status' => 'ERROR',
                            'msg' => 'El turno no ha sido iniciado'
                        );
                    } else {
                        // Calcular horas trabajadas
                        $actual_start = $shift_data['actual_start'];
                        $break_duration = isset($shift_data['break_duration']) ? $shift_data['break_duration'] : 0;
                        
                        error_log("DEBUG end_shift: actual_start = " . $actual_start);
                        error_log("DEBUG end_shift: shift_date = " . $shift_date);
                        error_log("DEBUG end_shift: current_time = " . $current_time);
                        error_log("DEBUG end_shift: break_duration = " . $break_duration);
                        
                        // actual_start ahora es DATETIME completo (YYYY-MM-DD HH:MM:SS)
                        // No concatenar con shift_date
                        $start_timestamp = strtotime($actual_start);
                        $end_timestamp = strtotime($shift_date . ' ' . $current_time);
                        
                        error_log("DEBUG end_shift: start_timestamp = " . $start_timestamp);
                        error_log("DEBUG end_shift: end_timestamp = " . $end_timestamp);
                        
                        if ($start_timestamp === false || $end_timestamp === false) {
                            error_log("DEBUG end_shift: Error al convertir fechas a timestamp");
                            $json = array(
                                'status' => 'ERROR',
                                'msg' => 'Error al calcular horas trabajadas'
                            );
                        } else {
                            $total_worked_minutes = round(($end_timestamp - $start_timestamp) / 60) - $break_duration;
                            error_log("DEBUG end_shift: total_worked_minutes (antes de validación) = " . $total_worked_minutes);
                            
                            // Validar valores negativos
                            if ($total_worked_minutes < 0) {
                                error_log("DEBUG end_shift: total_worked_minutes negativo, usando 0");
                                $total_worked_minutes = 0;
                            }

                            // Obtener configuración de horas regulares
                            $bind_setting = array('setting_key' => 'regular_hours_limit');
                            $setting_response = $obj_settings->get_setting($bind_setting);
                            $regular_hours_limit = $setting_response['status'] === 'OK' ? intval($setting_response['result']) : 8;

                            // Calcular horas regulares y extra
                            $total_worked_hours = $total_worked_minutes / 60;
                            $regular_hours = min($total_worked_hours, $regular_hours_limit);
                            $overtime_hours = max(0, $total_worked_hours - $regular_hours_limit);
                            
                            error_log("DEBUG end_shift: total_worked_hours = " . $total_worked_hours);
                            error_log("DEBUG end_shift: regular_hours = " . $regular_hours);
                            error_log("DEBUG end_shift: overtime_hours = " . $overtime_hours);

                            // Finalizar turno
                            $bind_update = array(
                                'shift_id' => $shift_id,
                                'actual_end' => $current_time,
                                'total_worked_minutes' => $total_worked_minutes,
                                'regular_hours' => number_format($regular_hours, 2),
                                'overtime_hours' => number_format($overtime_hours, 2),
                                'status' => 'completed'
                            );
                            $obj_shifts->update_shift_end($bind_update);

                            // Recalcular datos del turno para asegurar consistencia
                            $obj_shifts->recalculate_shift_data($shift_id);

                            // Registrar marcación
                            $bind_record = array(
                                'shift_id' => $shift_id,
                                'record_type' => 'check_out',
                                'record_time' => date('Y-m-d H:i:s'),
                                'location' => null,
                                'ip_address' => $_SERVER['REMOTE_ADDR'],
                                'user_agent' => $_SERVER['HTTP_USER_AGENT']
                            );
                            $obj_records->create_record($bind_record);

                            $json = array(
                                'status' => 'OK',
                                'msg' => 'Turno finalizado correctamente',
                                'total_worked_minutes' => $total_worked_minutes,
                                'regular_hours' => $regular_hours,
                                'overtime_hours' => $overtime_hours
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Excepción: ' . $e->getMessage()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        echo json_encode($json);
    }

    public function get_current_shift() {
        header('Content-Type: application/json');

        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $obj_shifts = $this->load_model('Attendance_Shifts');
                $obj_records = $this->load_model('Attendance_Records');

                $employee_id = $this->session->get('employee_id');
                $shift_date = date('Y-m-d');

                error_log("DEBUG get_current_shift: employee_id = " . $employee_id . ", shift_date = " . $shift_date);

                if (!$employee_id) {
                    error_log("DEBUG get_current_shift: employee_id es NULL");
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'Sesión inválida: employee_id no encontrado'
                    );
                    echo json_encode($json);
                    return;
                }

                $bind_shift = array(
                    'employee_id' => $employee_id,
                    'shift_date' => $shift_date
                );
                $shift = $obj_shifts->get_shift_by_employee_date($bind_shift);

                error_log("DEBUG get_current_shift: shift response = " . print_r($shift, true));

                if ($shift['status'] === 'OK') {
                    $shift_data = $shift['result'];

                    // Obtener marcaciones
                    $bind_records = array('shift_id' => $shift_data['id']);
                    $records = $obj_records->get_records_by_shift($bind_records);

                    $json = array(
                        'status' => 'OK',
                        'msg' => 'Turno encontrado',
                        'shift' => $shift_data,
                        'records' => $records['status'] === 'OK' ? $records['result'] : array()
                    );
                } else {
                    $json = array(
                        'status' => 'OK',
                        'msg' => 'No existe turno para hoy',
                        'shift' => null,
                        'records' => array()
                    );
                }
            } catch (Exception $e) {
                error_log("DEBUG get_current_shift: Exception = " . $e->getMessage());
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Excepción: ' . $e->getMessage()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        echo json_encode($json);
    }

    public function get_shift_history() {
        header('Content-Type: application/json');

        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $obj_shifts = $this->load_model('Attendance_Shifts');

                $employee_id = $this->session->get('employee_id');
                $bind = array('employee_id' => $employee_id);

                $response = $obj_shifts->get_employee_shifts($bind);

                if ($response['status'] === 'OK') {
                    $json = array(
                        'status' => 'OK',
                        'msg' => 'Historial obtenido',
                        'shifts' => $response['result']
                    );
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'No se encontraron turnos',
                        'shifts' => array()
                    );
                }
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Excepción: ' . $e->getMessage()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        echo json_encode($json);
    }
}
