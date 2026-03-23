<?php 
// --
class C_Creditsale extends Controller {

    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Creditsale');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Creditsale')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }
    public function get_creditsale()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }

            $campus_id = $this->segment->get('current_campus_id');

            if (!$campus_id) {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se ha seleccionado una ubicación.',
                    'data' => array()
                );
            } else {
                $obj = $this->load_model('Billingpersale');
                $response = $obj->get_creditsale($campus_id);

                switch ($response['status']) {
                    case 'OK':
                        // Verificar documentos con status 1
                        $pending_docs = array_filter($response['result'], function ($item) {
                            return $item['status'] === '1';
                        });

                        $warning_message = '';
                        if (!empty($pending_docs)) {
                            $count_pending = count($pending_docs);
                            $warning_message = array(
                                'show' => true,
                                'count' => $count_pending,
                                'message' => "¡Atención! Tiene {$count_pending} documento(s) pendiente(s) de declarar a SUNAT. " .
                                    "Estos documentos deben ser declarados dentro del plazo establecido para evitar multas.",
                                'action' => 'showPending'
                            );
                        }

                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Listado de registros encontrados.',
                            'data' => $response['result'],
                            'warning' => $warning_message
                        );
                        break;

                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron registros en el sistema.',
                            'data' => array(),
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
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_creditsale_by_id()
    {
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
            if (!empty($input['id_billingpersale'])) {
                // --
                $obj = $this->load_model('Billingpersale');
                // --
                $bind = array(
                    'id_billingpersale' => intval($input['id_billingpersale'])
                );
                // --
                $response = $obj->get_creditsale_by_id($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Listado de registros encontrados.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron registros en el sistema.',
                            'data' => array(),
                        );
                        // --
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        // --
                        break;
                }
            } else {
                // --
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            // --
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

}