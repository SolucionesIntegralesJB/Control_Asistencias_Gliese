<?php 
// --
class C_Income_Pending extends Controller {

    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Income');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Income')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    public function get_income_products_pending() {

        $this->functions->validate_session($this->segment->get('isActive'));

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $obj = $this->load_model('Income_Pending');
            $response = $obj->get_income_products_pending(); // Obtenemos datos del modelo

        
            if (isset($response['status']) && $response['status'] === 'OK' && !empty($response['result'])) {
                $json = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Listado de registros encontrados.',
                    'data' => $response['result']
                ];
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron registros en el sistema.',
                    'data' => []
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => []
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
        exit;
    }
    
    public function update_income_products_status() {
        $this->functions->validate_session($this->segment->get('isActive'));
    
        header('Content-Type: application/json');
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => []
            ]);
            exit;
        }
    
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (empty($data['registros']) || !is_array($data['registros'])) {
            echo json_encode([
                'status' => 'ERROR',
                'type' => 'warning',
                'msg' => 'No se recibieron registros válidos para actualizar.',
                'data' => []
            ]);
            exit;
        }
    
        $obj = $this->load_model('Income_Pending');
        $response = $obj->update_income_products_status($data['registros']);
    
        if (!$response || !is_array($response)) {
            echo json_encode([
                'status' => 'ERROR',
                'msg' => 'El modelo no devolvió una respuesta válida.'
            ]);
            exit;
        }
    
        echo json_encode($response);
        exit;
    }
    
    
}