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
            
            if (isset($input['user']) && isset($input['password'])) {
                $obj_login = $this->load_model('Login');
                $user = $this->functions->clean_string(trim($input['user']));
                $password = $this->functions->encrypt_password($input['password']);
                
                $bind = array(
                    'user' => $user,
                    'password' => $password
                );
                
                $response = $obj_login->get_user($bind);
                
                if ($response['status'] === 'OK' && !empty($response['result'])) {
                    $result_user = $response['result'][0];
                    
                    $this->session->set('is_logged', true);
                    $this->session->set('user_id', $result_user['id']);
                    $this->session->set('user_name', $result_user['first_name']);
                    $this->session->set('user_last_name', $result_user['last_name']);
                    $this->session->set('user_email', $result_user['email']);
                    $this->session->set('user_role', $result_user['id_role']);
                    $this->session->set('login_time', time());
                    
                    $json = array(
                        'status' => 'OK',
                        'msg' => 'Bienvenido(a) ' . $result_user['first_name'],
                        'redirect' => BASE_URL . 'Dashboard'
                    );
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'Credenciales incorrectas o usuario inactivo'
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
