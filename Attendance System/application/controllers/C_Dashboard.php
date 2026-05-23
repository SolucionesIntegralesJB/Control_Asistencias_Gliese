<?php
// Dashboard Controller - Attendance System
class C_Dashboard extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->functions->validate_session($this->session->get('is_logged'));
        
        $user_id = $this->session->get('user_id');
        
        // Obtener turno activo del día
        $obj_shifts = $this->load_model('Attendance_Shifts');
        $current_shift = $obj_shifts->get_current_active_shift(array('user_id' => $user_id));
        
        // Obtener estadísticas del usuario
        $statistics = $obj_shifts->get_user_statistics(array('user_id' => $user_id));
        
        // Determinar estado del turno
        $shift_status = 'Sin turno';
        if ($current_shift['status'] === 'OK') {
            $shift = $current_shift['result'];
            if ($shift['status'] === 'in_progress') {
                $shift_status = 'Turno activo';
            } elseif ($shift['status'] === 'completed') {
                $shift_status = 'Turno finalizado';
            } elseif ($shift['break_start'] && !$shift['break_end']) {
                $shift_status = 'En break';
            }
        }
        
        $data = array(
            'user_name' => $this->session->get('user_name'),
            'user_last_name' => $this->session->get('user_last_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'login_time' => $this->session->get('login_time'),
            'current_shift' => $current_shift['status'] === 'OK' ? $current_shift['result'] : null,
            'shift_status' => $shift_status,
            'statistics' => $statistics['status'] === 'OK' ? $statistics['result'] : null
        );
        
        $this->view->set_data($data);
        $this->view->set_view('index');
    }
}
