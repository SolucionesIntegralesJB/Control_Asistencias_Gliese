<?php
// --
class C_Cotize extends Controller {

    // --
    public function __construct() {
        parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Cotize');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Cotize')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_client_data() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }
            // --
            if (!empty($input['id_client'])) {
                // --
                $obj = $this->load_model('Cotize');
                // --
                $bind = array(
                    'id_client' => intval($input['id_client'])
                );
                // --
                $response = $obj->get_client_data($bind);
                // --
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Datos del cliente encontrados.',
                            'data' => $response['result']
                        );
                        break;

                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron datos del cliente.',
                            'data' => array()
                        );
                        break;

                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        break;
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function save_cotize() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            // --
            if (!empty($input['person_id']) && !empty($input['details'])) {
                // --
                $obj = $this->load_model('Cotize');
                // --
                $bind = array(
                    'person_id' => intval($input['person_id']),
                    'user_id' => $this->segment->get('id'),
                    'date_issue' => date('Y-m-d'),
                    'reference' => $input['reference'],
                    'cotize_type' => $input['cotize_type'],
                    'offer_validity' => $input['offer_validity'],
                    'subtotal' => $input['subtotal'],
                    'igv' => $input['igv'],
                    'total' => $input['total'],
                    'details' => $input['details']
                );
                // --
                $response = $obj->save_cotize($bind);
                // --
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Cotización guardada exitosamente.',
                            'data' => $response['result']
                        );
                        break;

                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible guardar la cotización.',
                            'data' => array()
                        );
                        break;

                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        break;
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_cotize() {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            
            $obj = $this->load_model('Cotize');
            $response = $obj->get_cotize();
            
            // Formatear los datos para DataTables
            if ($response['status'] === 'OK' && !empty($response['result'])) {
                foreach ($response['result'] as &$item) {
                    // Solo mostrar el botón de eliminar, quitando los botones de edición y visualización
                    $item['actions'] = '
                        <div class="d-flex">
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteCotizacion('.$item['id'].')" title="Anular cotización">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                    
                    // Formatear estado
                    switch ($item['status']) {
                        case 1:
                            $item['status_formatted'] = '<span class="badge bg-success">Activo</span>';
                            break;
                        case 2:
                            $item['status_formatted'] = '<span class="badge bg-warning">Pendiente</span>';
                            break;
                        case 3:
                            $item['status_formatted'] = '<span class="badge bg-danger">Anulado</span>';
                            break;
                        default:
                            $item['status_formatted'] = '<span class="badge bg-secondary">Desconocido</span>';
                    }
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode($response);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
        }
    }

    // --
    public function delete_cotize() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            if (!empty($_POST['id'])) {
                // --
                $obj = $this->load_model('Cotize');
                // --
                $bind = array(
                    'id' => intval($_POST['id'])
                );
                // --
                $response = $obj->delete_cotize($bind);
                // --
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Cotización eliminada correctamente.',
                            'data' => array()
                        );
                        break;

                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se pudo eliminar la cotización.',
                            'data' => array()
                        );
                        break;

                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        break;
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_quotation_details() {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            
            if (!isset($_GET['id']) || empty($_GET['id'])) {
                throw new Exception('ID de cotización no proporcionado');
            }
            
            $id = $_GET['id'];
            
            $model = $this->load_model('Cotize_Details');
            $response = $model->get_cotize_by_id($id);
            
            ob_clean(); // Limpiar cualquier salida anterior
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
            
        } catch (Exception $e) {
            ob_clean(); // Limpiar cualquier salida anterior
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // --
    public function update_quotation() {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            // Validar datos necesarios
            if (empty($_POST['cotize_id'])) {
                throw new Exception('ID de cotización no proporcionado');
            }
            
            if (empty($_POST['business_name_cli'])) {
                throw new Exception('El cliente es requerido');
            }
            
            if (empty($_POST['details'])) {
                throw new Exception('No hay productos agregados');
            }
            
            // Decodificar los detalles
            $_POST['details'] = json_decode($_POST['details'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al procesar los detalles de la cotización');
            }
            
            // Agregar el user_id a los datos POST
            $_POST['user_id'] = $this->segment->get('id');
            
            $model = $this->load_model('Cotize_Details');
            $response = $model->update_cotize_details($_POST);
            
            ob_clean(); // Limpiar cualquier salida anterior
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
            
        } catch (Exception $e) {
            ob_clean(); // Limpiar cualquier salida anterior
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // --
    public function cancel_cotize() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            if (!empty($_POST['cotize_id'])) {
                // --
                $obj = $this->load_model('Cotize');
                // --
                $bind = array(
                    'cotize_id' => intval($_POST['cotize_id'])
                );
                // --
                $response = $obj->cancel_cotize($bind);
                // --
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Cotización anulada correctamente.',
                        );
                        break;
    
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se pudo anular la cotización.',
                        );
                        break;
    
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                        );
                        break;
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se envió el ID de la cotización, verificar.',
                );
            }
        } else {
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