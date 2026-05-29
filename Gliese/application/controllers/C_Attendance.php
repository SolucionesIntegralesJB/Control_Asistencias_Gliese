<?php
// --
class C_Attendance extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Attendance');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Attendance')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_attendance_list()
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
            $obj = $this->load_model('Attendance');
            // --
            $bind = array();
            
            // -- Apply filters
            if (!empty($input['employee_id'])) {
                $bind['employee_id'] = intval($input['employee_id']);
            }
            if (!empty($input['start_date'])) {
                $bind['start_date'] = $this->functions->clean_string($input['start_date']);
            }
            if (!empty($input['end_date'])) {
                $bind['end_date'] = $this->functions->clean_string($input['end_date']);
            }
            if (!empty($input['status'])) {
                $bind['status'] = $this->functions->clean_string($input['status']);
            }
            if (!empty($input['campus_id'])) {
                $bind['campus_id'] = intval($input['campus_id']);
            }
            if (!empty($input['limit'])) {
                $bind['limit'] = intval($input['limit']);
            }
            if (!empty($input['offset'])) {
                $bind['offset'] = intval($input['offset']);
            }
            
            // --
            $response = $obj->get_attendance_list($bind);
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
    public function get_attendance_count()
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
            $obj = $this->load_model('Attendance');
            // --
            $bind = array();
            
            // -- Apply filters
            if (!empty($input['employee_id'])) {
                $bind['employee_id'] = intval($input['employee_id']);
            }
            if (!empty($input['start_date'])) {
                $bind['start_date'] = $this->functions->clean_string($input['start_date']);
            }
            if (!empty($input['end_date'])) {
                $bind['end_date'] = $this->functions->clean_string($input['end_date']);
            }
            if (!empty($input['status'])) {
                $bind['status'] = $this->functions->clean_string($input['status']);
            }
            if (!empty($input['campus_id'])) {
                $bind['campus_id'] = intval($input['campus_id']);
            }
            
            // --
            $response = $obj->get_attendance_count($bind);
            // --
            switch ($response['status']) {
                    // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Total de registros.',
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
                        'data' => array('total' => 0),
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
    public function get_attendance_detail()
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
            if (!empty($input['id'])) {
                // --
                $obj = $this->load_model('Attendance');
                // --
                $bind = array(
                    'id' => intval($input['id'])
                );
                // --
                $response = $obj->get_attendance_detail($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Detalle del turno encontrado.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontró el turno en el sistema.',
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

    // --
    public function get_shift_events()
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
            if (!empty($input['shift_id'])) {
                // --
                $obj = $this->load_model('Attendance');
                // --
                $bind = array(
                    'shift_id' => intval($input['shift_id'])
                );
                // --
                $response = $obj->get_shift_events($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Eventos del turno encontrados.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron eventos del turno.',
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

    // --
    public function calculate_payment()
    {
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
            if (!empty($input['id'])) {
                // --
                $obj = $this->load_model('Attendance');
                // --
                $bind = array(
                    'id' => intval($input['id']),
                    'created_by' => isset($this->segment->get('user')['id']) ? $this->segment->get('user')['id'] : null
                );
                // --
                $response = $obj->calculate_payment($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Pago calculado con éxito.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => isset($response['result']['msg']) ? $response['result']['msg'] : 'No fue posible calcular el pago, verificar.',
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

    // --
    public function update_shift()
    {
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
            if (!empty($input['id'])) {
                // --
                $obj = $this->load_model('Attendance');
                // --
                $bind = array(
                    'id' => intval($input['id']),
                    'created_by' => isset($this->segment->get('user')['id']) ? $this->segment->get('user')['id'] : null
                );
                
                // -- Add optional fields
                if (!empty($input['actual_start'])) {
                    $bind['actual_start'] = $this->functions->clean_string($input['actual_start']);
                }
                if (!empty($input['actual_end'])) {
                    $bind['actual_end'] = $this->functions->clean_string($input['actual_end']);
                }
                if (!empty($input['break_start'])) {
                    $bind['break_start'] = $this->functions->clean_string($input['break_start']);
                }
                if (!empty($input['break_end'])) {
                    $bind['break_end'] = $this->functions->clean_string($input['break_end']);
                }
                if (!empty($input['hourly_rate'])) {
                    $bind['hourly_rate'] = floatval($input['hourly_rate']);
                }
                if (!empty($input['overtime_rate'])) {
                    $bind['overtime_rate'] = floatval($input['overtime_rate']);
                }
                
                // --
                $response = $obj->update_shift($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Turno actualizado con éxito.',
                            'data' => array()
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => isset($response['result']['msg']) ? $response['result']['msg'] : 'No fue posible actualizar el turno, verificar.',
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

    // --
    public function get_employees_list()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj = $this->load_model('Attendance');
            // --
            $response = $obj->get_employees_list();
            // --
            switch ($response['status']) {
                    // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de empleados encontrados.',
                        'data' => $response['result']
                    );
                    // --
                    break;

                case 'ERROR':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron empleados en el sistema.',
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
    public function get_campus_list()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj = $this->load_model('Attendance');
            // --
            $response = $obj->get_campus_list();
            // --
            switch ($response['status']) {
                    // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de sedes encontrados.',
                        'data' => $response['result']
                    );
                    // --
                    break;

                case 'ERROR':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron sedes en el sistema.',
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
