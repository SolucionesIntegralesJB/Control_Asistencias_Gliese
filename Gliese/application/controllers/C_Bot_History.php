<?php
// --
class C_Bot_History extends Controller
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function index()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Bot_History');
        // --
        $this->view->set_js('index'); // -- Load JS
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'),
            'view' => 'Bot_History',
        ]); // -- Active Menu
        $this->view->set_view('index'); // -- Load View
    }

    // --
    public function get_history()
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
            $obj = $this->load_model('Bot_History');
            // --
            $response = $obj->get_history();
            // --
            switch ($response['status']) {
                // --
                case 'OK':
                    // --
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de registros encontrados.',
                        'data' => $response['result'],
                    ];
                    // --
                    break;

                case 'ERROR':
                    // --
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron registros en el sistema.',
                    ];
                    // --
                    break;

                case 'EXCEPTION':
                    // --
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
                    ];
                    // --
                    break;
            }
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_historial_stats()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj = $this->load_model('Bot_History');
            // --
            $response = $obj->get_statistics();
            // --
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron estadísticas.',
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_user_detail()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $input = filter_input_array(INPUT_GET);
            // --
            if (isset($input['phone'])) {
                $obj = $this->load_model('Bot_History');
                $response = $obj->get_user_detail($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'data' => $response['result'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontró el usuario.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetro phone requerido.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
