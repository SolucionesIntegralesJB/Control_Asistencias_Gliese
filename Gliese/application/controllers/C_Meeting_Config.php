<?php
/**
 * Controlador de Configuraciones de Reuniones
 * Gestión centralizada de enlaces Meet y horarios
 */
class C_Meeting_Config extends Controller {

    public function __construct() {
        parent::__construct();
    }

    // Vista principal
    public function index() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->view->set_js('index');
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Meeting_Config'));
        $this->view->set_view('index');
    }

    // GET: Obtener todas las configuraciones
    public function get_configs() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Meeting_Config');
            $response = $obj->get_all_configs();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result']
                    ];
                    break;
                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => []
                    ];
                    break;
                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                        'data' => []
                    ];
                    break;
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // POST: Crear nueva configuración
    public function create_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['name']) && isset($input['meet_link'])) {
                $input['created_by'] = $this->segment->get('id_user');
                $obj = $this->load_model('Meeting_Config');
                $response = $obj->create_config($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Configuración creada correctamente.',
                            'data' => $response['result']
                        ];
                        break;
                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        ];
                        break;
                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        ];
                        break;
                }
            } else {
                $json = ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'Nombre y enlace son requeridos.'];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // PUT: Actualizar configuración existente
    public function update_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id'])) {
                $obj = $this->load_model('Meeting_Config');
                $response = $obj->update_config($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Configuración actualizada correctamente.'
                        ];
                        break;
                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        ];
                        break;
                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        ];
                        break;
                }
            } else {
                $json = ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID requerido.'];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // DELETE: Eliminar configuración
    public function delete_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id'])) {
                $obj = $this->load_model('Meeting_Config');
                $response = $obj->delete_config($input['id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Configuración eliminada correctamente.'
                        ];
                        break;
                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        ];
                        break;
                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        ];
                        break;
                }
            } else {
                $json = ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID requerido.'];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // PUT: Asignar configuración a grupos
    public function assign_to_groups() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['config_id']) && isset($input['group_ids'])) {
                $obj = $this->load_model('Meeting_Config');
                $response = $obj->assign_to_groups($input['config_id'], $input['group_ids']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Configuración asignada correctamente a ' . count($input['group_ids']) . ' grupo(s).'
                        ];
                        break;
                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        ];
                        break;
                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        ];
                        break;
                }
            } else {
                $json = ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID de configuración y grupos requeridos.'];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // GET: Obtener grupos asignados a una configuración
    public function get_assigned_groups() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $input = filter_input_array(INPUT_GET);

            if (isset($input['config_id'])) {
                $obj = $this->load_model('Meeting_Config');
                $response = $obj->get_assigned_groups($input['config_id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'data' => $response['result']
                        ];
                        break;
                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'],
                            'data' => []
                        ];
                        break;
                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                            'data' => []
                        ];
                        break;
                }
            } else {
                $json = ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID de configuración requerido.', 'data' => []];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => []];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
