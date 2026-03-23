<?php
// --
class C_Location extends Controller {

    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        $this->view->set_js('index');
        $this->view->set_view('default');
    }

    // --
    public function get_locations_by_user() {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $dataUser = $this->segment->get('data');
            // --
            if ($dataUser["id_user"]) {
                // --
                $id_user = $dataUser["id_user"];
                // --
                $bind = array(
                    'id_user' => $id_user
                );
            
                $obj_users = $this->load_model('Users');
                // --
                $response = $obj_users->get_locations_by_user($bind);
                // --
                $id_location = $this->segment->get('isLocation');
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $result_user = $response['result'][0];
                        $campus = [];
                        $location = "";
                        $this->segment->set('current_campus_id', $id_location);
                        // --
                        foreach ($response['result'] as $item) {
                            $campus[] = array(
                                'id' => $item['id_campus'],
                                'description' => $item['campus']
                            );

                            if($item['id_campus'] == $id_location){
                                $location = $item['campus'];
                            }
                        }
                        // --
                        $user = array(
                            'id_user' => $result_user['id_user'],
                            'id_location' => $id_location,
                            'location' => $location,
                            'role' => $result_user['role'],
                            'user' => $result_user['user'],
                            'first_name' => $result_user['first_name'],
                            'last_name' => $result_user['last_name'],
                        );
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Listado de registros encontrados.',
                            'data' => array(
                                'user' => $user,
                                'campus' => $campus
                            ),
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron registros en el sistema.',
                        );
                        // --
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                        );
                        // --
                        break;
                }
            }else{
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error de sesión, intentelo de nuevo.',
                ); 
            }
            
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ); 
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    
}
