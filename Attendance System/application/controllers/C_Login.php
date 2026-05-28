<?php
// Login Controller - Attendance System
class C_Login extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->functions->check_session($this->session->get('is_logged'));
        $this->view->set_view('index');
    }

    public function login() {
        $request = $_SERVER['REQUEST_METHOD'];
        
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            
            if (isset($input['email']) && isset($input['password'])) {
                $obj_login = $this->load_model('Login');
                $email = $this->functions->clean_string(trim($input['email']));
                $password = $input['password']; // Contraseña en texto plano para password_verify()

                $bind = array(
                    'email' => $email,
                    'password' => $password
                );

                $response = $obj_login->get_user($bind);
                
                if ($response['status'] === 'OK' && !empty($response['result'])) {
                    $result_employee = $response['result'][0];
                    
                    $this->session->set('is_logged', true);
                    $this->session->set('employee_id', $result_employee['id']);
                    $this->session->set('employee_name', $result_employee['name']);
                    $this->session->set('employee_email', $result_employee['email']);
                    $this->session->set('employee_position', $result_employee['position']);
                    $this->session->set('employee_work_area', $result_employee['work_area']);
                    $this->session->set('employee_role_id', $result_employee['role_person_id']);
                    $this->session->set('login_time', time());
                    
                    $json = array(
                        'status' => 'OK',
                        'msg' => 'Bienvenido(a) ' . $result_employee['name'],
                        'redirect' => BASE_URL . 'Dashboard'
                    );
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'Credenciales incorrectas o empleado inactivo'
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

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function logout() {
        $this->session->destroy();
        header('Location: ' . BASE_URL);
        exit();
    }
}
