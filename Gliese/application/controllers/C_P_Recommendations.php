<?php
// --
class C_P_Recommendations extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'P_Recommendations');
        $this->view->set_js('index');
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'P_Recommendations'));
        $this->view->set_view('index');
    }

    public function get_recommendations() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('P_Recommendations');
            $response = $obj->get_all();
            switch ($response['status']) {
                case 'OK':
                    $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registros encontrados', 'data' => $response['result']);
                    break;
                case 'ERROR':
                    $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No hay registros');
                    break;
                case 'EXCEPTION':
                    $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                    break;
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function create() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = filter_input_array(INPUT_POST);
            if (!empty($input['id_user']) && !empty($input['recommendation_text'])) {
                $bind = array(
                    'id_user' => $this->functions->clean_string($input['id_user']),
                    'recommendation_text' => $this->functions->clean_string($input['recommendation_text']),
                    'recommendation_type' => isset($input['recommendation_type']) ? $this->functions->clean_string($input['recommendation_type']) : 'POSITIVA',
                    'created_by' => $this->segment->get('data')['id_user']
                );
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->create($bind);

                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro creado.');
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se pudo crear.');
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Faltan campos');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function update() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = filter_input_array(INPUT_POST);
            if (!empty($input['id']) && !empty($input['recommendation_text'])) {
                $bind = array(
                    'id' => $this->functions->clean_string($input['id']),
                    'recommendation_text' => $this->functions->clean_string($input['recommendation_text']),
                    'recommendation_type' => isset($input['recommendation_type']) ? $this->functions->clean_string($input['recommendation_type']) : 'POSITIVA'
                );
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->update($bind);

                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro actualizado.');
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se pudo actualizar.');
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Faltan campos');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function delete() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            if (!empty($input['id'])) {
                $bind = array('id' => $this->functions->clean_string($input['id']));
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->delete($bind);
                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro eliminado.');
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se pudo eliminar.');
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Falta id');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // Spanish-compatible endpoints used by the frontend JS
    public function get_recomendaciones() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('P_Recommendations');
            $response = $obj->get_all();
            if ($response['status'] === 'OK') {
                // Map fields to the format expected by the frontend JS
                $items = array_map(function($r){
                    $autor = '';
                    if (!empty($r['creator_first']) || !empty($r['creator_last'])) {
                        $autor = trim(($r['creator_first'] ?? '') . ' ' . ($r['creator_last'] ?? ''));
                    } else {
                        // fallback: if created_by is null or creator empty, show 'Sistema'
                        $autor = 'Sistema';
                    }
                    return array(
                        'id' => $r['id'],
                        'titulo' => $r['title'] ?? ($r['title'] ?? ''),
                        'contenido' => $r['description'] ?? '',
                        'autor' => $autor,
                        'fecha_creacion' => $r['created_at'] ?? '',
                        'status' => isset($r['status']) ? (int)$r['status'] : 1
                    );
                }, $response['result']);
                $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registros encontrados', 'data' => $items);
            } elseif ($response['status'] === 'ERROR') {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No hay registros', 'data' => array());
            } else {
                $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage(), 'data' => array());
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => array());
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_recomendacion_detail() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $id = isset($_GET['id']) ? $this->functions->clean_string($_GET['id']) : null;
            if ($id) {
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->get_by_id($id);
                if ($response['status'] === 'OK') {
                    $r = $response['result'];
                    $autor = '';
                    if (!empty($r['creator_first']) || !empty($r['creator_last'])) {
                        $autor = trim(($r['creator_first'] ?? '') . ' ' . ($r['creator_last'] ?? ''));
                    } else {
                        $autor = 'Sistema';
                    }
                    $item = array(
                        'id' => $r['id'],
                        'titulo' => $r['title'] ?? '',
                        'contenido' => $r['description'] ?? '',
                        'autor' => $autor,
                        'fecha_creacion' => $r['created_at'] ?? '',
                        'status' => isset($r['status']) ? (int)$r['status'] : 1
                    );
                    $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro encontrado', 'data' => $item);
                } elseif ($response['status'] === 'ERROR') {
                    $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No existe registro', 'data' => array());
                } else {
                    $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage(), 'data' => array());
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Falta id', 'data' => array());
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => array());
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function crear_recomendacion() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        // accept POST or JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = filter_input_array(INPUT_POST);
        if ($request === 'POST' || $request === 'PUT') {
            if (!empty($input['titulo']) && !empty($input['contenido'])) {
                $bind = array(
                    'title' => $this->functions->clean_string($input['titulo']),
                    'description' => $this->functions->clean_string($input['contenido']),
                    'created_by' => $this->segment->get('data')['id_user']
                );
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->create($bind);
                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro creado.', 'data' => $response['result']);
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se pudo crear.', 'data' => array());
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage(), 'data' => array());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Faltan campos', 'data' => array());
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => array());
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function editar_recomendacion() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = filter_input_array(INPUT_POST);
        if ($request === 'POST' || $request === 'PUT') {
            if (!empty($input['id']) && !empty($input['titulo']) && !empty($input['contenido'])) {
                $bind = array(
                    'id' => $this->functions->clean_string($input['id']),
                    'title' => $this->functions->clean_string($input['titulo']),
                    'description' => $this->functions->clean_string($input['contenido'])
                );
                // Optional: allow updating status when provided (1 = active, 0 = inactive)
                if (isset($input['status'])) {
                    $bind['status'] = (int) $this->functions->clean_string($input['status']);
                }
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->update($bind);
                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro actualizado.', 'data' => array());
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se pudo actualizar.', 'data' => array());
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage(), 'data' => array());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Faltan campos', 'data' => array());
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => array());
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function eliminar_recomendacion() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = filter_input_array(INPUT_POST);
        if ($request === 'POST' || $request === 'DELETE') {
            if (!empty($input['id'])) {
                $bind = array('id' => $this->functions->clean_string($input['id']));
                $obj = $this->load_model('P_Recommendations');
                $response = $obj->delete($bind);
                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro eliminado.', 'data' => array());
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se pudo eliminar.', 'data' => array());
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage(), 'data' => array());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Falta id', 'data' => array());
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => array());
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

}
