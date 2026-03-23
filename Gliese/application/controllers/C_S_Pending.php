<?php
// --
class C_S_Pending extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'S_Pending');
        // --
        $this->view->set_js('index'); // -- Load JS
        $this->view->set_menu(['modules' => $this->segment->get('modules'), 'view' => 'S_Pending']); // -- Active Menu
        $this->view->set_view('index'); // -- Load View
    }

    // --
    public function get_pending()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            // Obtener el estado del filtro (por defecto: pendiente)
            $status = isset($_GET['status']) ? $_GET['status'] : 'pendiente';

            $obj = $this->load_model('S_Pending');
            $response = $obj->get_pending($status);
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de solicitudes.',
                        'data' => $response['result'],
                    ];
                    break;
                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay solicitudes con estado: ' . $status,
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
    public function get_student_detail()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            $input = filter_input_array(INPUT_GET);

            if (isset($input['id'])) {
                $obj = $this->load_model('S_Pending');
                $response = $obj->get_student_by_id($input);

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
                            'msg' => 'No se encontró el practicante.',
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
                    'msg' => 'Parámetro id requerido.',
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

    // --
    public function aceptar_solicitud()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id_student'])) {
                // Si no viene template_used desde el frontend, lo asignamos por defecto
                if (!isset($input['template_used'])) {
                    $input['template_used'] = 'aceptado';
                }
                $obj = $this->load_model('S_Pending');
                $response = $obj->aceptar_solicitud($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Solicitud aceptada correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al aceptar la solicitud.',
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
                    'msg' => 'Parámetro id_student requerido.',
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

    // --
    public function rechazar_solicitud()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id_student'])) {
                // Si no viene template_used desde el frontend, lo asignamos por defecto
                if (!isset($input['template_used'])) {
                    $input['template_used'] = 'rechazado';
                }
                $obj = $this->load_model('S_Pending');
                $response = $obj->rechazar_solicitud($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Solicitud rechazada.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al rechazar la solicitud.',
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
                    'msg' => 'Parámetro id_student requerido.',
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

    // --
    public function get_templates()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj = $this->load_model('S_Pending');
            // --
            $response = $obj->get_templates();
            // --
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Plantillas cargadas.',
                        'data' => $response['result'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron plantillas.',
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
    public function update_templates()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['type']) && isset($input['subject']) && isset($input['content'])) {
                $obj = $this->load_model('S_Pending');
                $response = $obj->update_templates($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Plantilla actualizada correctamente.',
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
                    'msg' => 'Parámetros requeridos: tipo, asunto, contenido.',
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
