<?php
// Main Controller - Attendance System
class C_Main extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $this->view->set_view('index');
    }

    public function get_role() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'GET') {
            $obj_main = $this->load_model('Main');
            $response = $obj_main->get_role();
            
            if ($response['status'] === 'OK') {
                $json = array(
                    'status' => 'OK',
                    'data' => $response['result']
                );
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron roles',
                    'data' => array()
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

    public function get_campus() {
        $this->functions->validate_session($this->session->get('is_logged'));
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'GET') {
            $obj_main = $this->load_model('Main');
            $response = $obj_main->get_campus();
            
            if ($response['status'] === 'OK') {
                $json = array(
                    'status' => 'OK',
                    'data' => $response['result']
                );
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron campus',
                    'data' => array()
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
