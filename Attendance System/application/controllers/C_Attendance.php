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
            'user_name' => $this->session->get('user_name'),
            'user_last_name' => $this->session->get('user_last_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role')
        );
        
        $this->view->set_data($data);
        $this->view->set_view('index');
    }

    public function start_shift() {
        // DEBUG: Log inicio del método
        error_log("DEBUG: start_shift() iniciado");
        
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        error_log("DEBUG: Método de solicitud: " . $request);
        
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            
            error_log("DEBUG: Input recibido: " . print_r($input, true));
            
            if (isset($input['job_role_id']) && isset($input['campus_id'])) {
                try {
                    error_log("DEBUG: Cargando modelos...");
                    $obj_shifts = $this->load_model('Attendance_Shifts');
                    $obj_records = $this->load_model('Attendance_Records');
                    
                    $user_id = $this->session->get('user_id');
                    $shift_date = date('Y-m-d');
                    $current_time = date('H:i:s');
                    
                    error_log("DEBUG: user_id=$user_id, shift_date=$shift_date, current_time=$current_time");
                    
                    // Validar: un solo turno por día
                    $bind_check = array(
                        'user_id' => $user_id,
                        'shift_date' => $shift_date
                    );
                    error_log("DEBUG: Verificando turno existente...");
                    $existing_shift = $obj_shifts->get_shift_by_user_date($bind_check);
                    error_log("DEBUG: Resultado verificación: " . print_r($existing_shift, true));
                    
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
                            'user_id' => $user_id,
                            'job_role_id' => $this->functions->clean_string($input['job_role_id']),
                            'campus_id' => $this->functions->clean_string($input['campus_id']),
                            'shift_date' => $shift_date,
                            'scheduled_start' => $scheduled_start,
                            'scheduled_end' => $scheduled_end,
                            'status' => 'pending'
                        );
                        
                        error_log("DEBUG: Creando turno con bind: " . print_r($bind_shift, true));
                        $response_shift = $obj_shifts->create_shift($bind_shift);
                        error_log("DEBUG: Respuesta create_shift: " . print_r($response_shift, true));
                        
                        if ($response_shift['status'] === 'OK') {
                            $shift_id = $response_shift['result']['shift_id'];
                            error_log("DEBUG: Turno creado con ID: $shift_id");
                            
                            // Actualizar hora de inicio
                            $bind_update = array(
                                'shift_id' => $shift_id,
                                'actual_start' => $current_time,
                                'status' => 'in_progress'
                            );
                            error_log("DEBUG: Actualizando hora de inicio...");
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
                            error_log("DEBUG: Registrando marcación check_in...");
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
                            error_log("DEBUG: Error al crear turno: $error_msg");
                            $json = array(
                                'status' => 'ERROR',
                                'msg' => $error_msg
                            );
                        }
                    }
                } catch (Exception $e) {
                    error_log("DEBUG: Excepción capturada: " . $e->getMessage());
                    error_log("DEBUG: Stack trace: " . $e->getTraceAsString());
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'Excepción: ' . $e->getMessage()
                    );
                }
            } else {
                error_log("DEBUG: Faltan parámetros job_role_id o campus_id");
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Verificar parámetros'
                );
            }
        } else {
            error_log("DEBUG: Método no permitido");
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        error_log("DEBUG: Respuesta JSON: " . print_r($json, true));
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function start_break() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'POST') {
            $obj_shifts = $this->load_model('Attendance_Shifts');
            $obj_records = $this->load_model('Attendance_Records');
            
            $user_id = $this->session->get('user_id');
            $shift_date = date('Y-m-d');
            $current_time = date('H:i:s');
            
            // Obtener turno actual
            $bind_shift = array(
                'user_id' => $user_id,
                'shift_date' => $shift_date
            );
            $shift = $obj_shifts->get_shift_by_user_date($bind_shift);
            
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
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function end_break() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'POST') {
            $obj_shifts = $this->load_model('Attendance_Shifts');
            $obj_records = $this->load_model('Attendance_Records');
            $obj_settings = $this->load_model('Attendance_Settings');
            
            $user_id = $this->session->get('user_id');
            $shift_date = date('Y-m-d');
            $current_time = date('H:i:s');
            
            // Obtener turno actual
            $bind_shift = array(
                'user_id' => $user_id,
                'shift_date' => $shift_date
            );
            $shift = $obj_shifts->get_shift_by_user_date($bind_shift);
            
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
                        $break_start_timestamp = strtotime($shift_date . ' ' . $break_start);
                        $break_end_timestamp = strtotime($shift_date . ' ' . $current_time);
                        $break_duration_minutes = round(($break_end_timestamp - $break_start_timestamp) / 60);
                        
                        // Finalizar break
                        $bind_update = array(
                            'shift_id' => $shift_id,
                            'break_end' => $current_time,
                            'break_duration' => $break_duration_minutes
                        );
                        $obj_shifts->update_break_end($bind_update);
                        
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
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function end_shift() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'POST') {
            $obj_shifts = $this->load_model('Attendance_Shifts');
            $obj_records = $this->load_model('Attendance_Records');
            $obj_settings = $this->load_model('Attendance_Settings');
            
            $user_id = $this->session->get('user_id');
            $shift_date = date('Y-m-d');
            $current_time = date('H:i:s');
            
            // Obtener turno actual
            $bind_shift = array(
                'user_id' => $user_id,
                'shift_date' => $shift_date
            );
            $shift = $obj_shifts->get_shift_by_user_date($bind_shift);
            
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
                    
                    $start_timestamp = strtotime($shift_date . ' ' . $actual_start);
                    $end_timestamp = strtotime($shift_date . ' ' . $current_time);
                    $total_worked_minutes = round(($end_timestamp - $start_timestamp) / 60) - $break_duration;
                    
                    // Obtener configuración de horas regulares
                    $bind_setting = array('setting_key' => 'regular_hours_limit');
                    $setting_response = $obj_settings->get_setting($bind_setting);
                    $regular_hours_limit = $setting_response['status'] === 'OK' ? intval($setting_response['result']) : 8;
                    
                    // Calcular horas regulares y extra
                    $total_worked_hours = $total_worked_minutes / 60;
                    $regular_hours = min($total_worked_hours, $regular_hours_limit);
                    $overtime_hours = max(0, $total_worked_hours - $regular_hours_limit);
                    
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
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_current_shift() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'GET') {
            $obj_shifts = $this->load_model('Attendance_Shifts');
            $obj_records = $this->load_model('Attendance_Records');
            
            $user_id = $this->session->get('user_id');
            $shift_date = date('Y-m-d');
            
            $bind_shift = array(
                'user_id' => $user_id,
                'shift_date' => $shift_date
            );
            $shift = $obj_shifts->get_shift_by_user_date($bind_shift);
            
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
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_shift_history() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'GET') {
            $obj_shifts = $this->load_model('Attendance_Shifts');
            
            $user_id = $this->session->get('user_id');
            $bind = array('user_id' => $user_id);
            
            $response = $obj_shifts->get_user_shifts($bind);
            
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
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
